<?php

use Illuminate\Support\Facades\Route;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

use App\Http\Controllers\BerandaController;
use App\Http\Controllers\DivisiController;
use App\Http\Controllers\GaleriAdminController;
use App\Http\Controllers\GaleriController;
use App\Http\Controllers\KontenController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PimpinanAdminController;
use App\Http\Controllers\SponsorController;
use App\Http\Controllers\SubAdminAdminController;
use App\Models\Calendar;
use App\Models\Divisi;
use App\Models\Konten;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Route Publik

Route::get('/', [BerandaController::class, 'index'])->name('beranda');
Route::get('/selengkapnya', [BerandaController::class, "selengkapnya"]);
Route::get('/divisi', [DivisiController::class, 'index']);
Route::get('/divisi/{divisi}', [DivisiController::class, 'show']);
Route::get('/divisi/{divisi}/{id_konten}', [KontenController::class, 'index']);
Route::get('/galeri', [GaleriController::class, 'index']);
Route::get('/selengkapnya', [BerandaController::class, 'selengkapnya']);
Route::get('/jadwal', [BerandaController::class, 'jadwal']);
Route::get('/konten/{slug}', [KontenController::class, 'show'])->name('konten.show');
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index']);
Route::get('/robots.txt', function () {
    $path = public_path('robots.txt');
    if (file_exists($path)) {
        return response(file_get_contents($path), 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }
    return response("User-agent: *\nAllow: /\nSitemap: " . url('/sitemap.xml'), 200, ['Content-Type' => 'text/plain; charset=utf-8']);
});


// Route Admin

Route::get('/login', function() {
    return view('admin.login.index');
})->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
Route::match(['get', 'post'], '/logout', [LoginController::class, 'logout'])->name('logout');

// Password Reset Routes (Forgot Password / Reset Password)
Route::get('/forgot-password', [\App\Http\Controllers\PasswordResetController::class, 'showForgotForm'])
    ->name('password.request');
Route::post('/forgot-password', [\App\Http\Controllers\PasswordResetController::class, 'sendResetLink'])
    ->name('password.email')
    ->middleware('throttle:5,1');
Route::get('/reset-password/{token}', [\App\Http\Controllers\PasswordResetController::class, 'showResetForm'])
    ->name('password.reset');
Route::post('/reset-password', [\App\Http\Controllers\PasswordResetController::class, 'resetPassword'])
    ->name('password.update');

Route::get('/gallery', function(Request $request) {
    if (!Auth::check()) {
        return redirect('/login');
    }

    $query = Konten::with(['divisi', 'user']);

    // Role authorization: regular admin only sees their assigned division
    if (Auth::user()->role === 'admin') {
        $query->where('divisi_uuid', Auth::user()->divisi_uuid);
    }

    // Server-side Search: Judul, Deskripsi (Konten), or Divisi name
    if ($request->filled('search')) {
        $search = trim($request->search);
        $cleanSearch = trim(preg_replace('/^(divisi|konten|judul)\s*[:\-]?\s*/i', '', $search));
        $effectiveSearch = $cleanSearch !== '' ? $cleanSearch : $search;

        $query->where(function ($q) use ($search, $effectiveSearch) {
            $q->where('judul', 'like', "%{$search}%")
              ->orWhere('deskripsi', 'like', "%{$search}%")
              ->orWhereHas('divisi', function ($divQuery) use ($search, $effectiveSearch) {
                  $divQuery->where('nama_divisi', 'like', "%{$search}%")
                           ->orWhere('nama_divisi', 'like', "%{$effectiveSearch}%");
              });

            if ($effectiveSearch !== $search) {
                $q->orWhere('judul', 'like', "%{$effectiveSearch}%")
                  ->orWhere('deskripsi', 'like', "%{$effectiveSearch}%");
            }
        });
    }

    // Server-side Media Filter: foto vs video
    if ($request->filled('media') && $request->media !== 'all') {
        if ($request->media === 'video') {
            $query->where(function ($q) {
                $q->where('url_media', 'like', '%.mp4%')
                  ->orWhere('url_media', 'like', '%.webm%')
                  ->orWhere('url_media', 'like', '%youtube%')
                  ->orWhere('url_media', 'like', '%youtu.be%');
            });
        } elseif ($request->media === 'foto' || $request->media === 'image') {
            $query->where(function ($q) {
                $q->where('url_media', 'like', '%.jpg%')
                  ->orWhere('url_media', 'like', '%.jpeg%')
                  ->orWhere('url_media', 'like', '%.png%')
                  ->orWhere('url_media', 'like', '%.webp%');
            });
        }
    }

    // Server-side Date Filter: specific date or month prefix
    if ($request->filled('date') && $request->date !== 'all') {
        $date = trim($request->date);
        $query->where('tanggal_upload', 'like', "{$date}%");
    }

    // Server-side Date Ordering: newest vs oldest
    if ($request->input('date_order') === 'oldest') {
        $query->orderBy('tanggal_upload', 'asc')->orderBy('uuid', 'asc');
    } else {
        $query->orderBy('tanggal_upload', 'desc')->orderBy('uuid', 'desc');
    }

    // Server-side Per-Page Whitelist
    $allowedPerPage = [2, 5, 10, 20, 50];
    $requestedPerPage = (int) $request->input('per_page');
    $perPage = in_array($requestedPerPage, $allowedPerPage) ? $requestedPerPage : 5;

    // Use standard page parameter with withQueryString()
    $pageName = $request->has('gallery-page') ? 'gallery-page' : 'page';
    $data = $query->paginate($perPage, ['*'], $pageName)->withQueryString();

    if ($request->ajax() && ($request->has('page') || $request->has('gallery-page') || $request->has('per_page'))) {
        return view('admin.gallery-table', compact('data'))->render();
    }

    return view('admin.main.gallery', compact('data'));
})->name('gallery.main');

// Autocomplete suggestion endpoints (Lightweight, max 8 items, term/q >= 2, structured JSON)
Route::get('/gallery/suggestions', function(Request $request) {
    if (!Auth::check()) {
        return response()->json([]);
    }
    $term = trim($request->input('term', $request->input('q', '')));
    if (mb_strlen($term) < 2) {
        return response()->json([]);
    }

    $lowerTerm = strtolower($term);
    $suggestions = collect();

    // Handle literal 'divisi' search -> return all available divisions
    if ($lowerTerm === 'divisi') {
        $divisiList = Divisi::all();
        foreach ($divisiList as $d) {
            $suggestions->push([
                'label' => 'Divisi ' . $d->nama_divisi,
                'sub' => 'Filter galeri berdasarkan divisi ' . $d->nama_divisi,
                'value' => $d->nama_divisi,
            ]);
        }
        return response()->json($suggestions->take(8)->values()->all());
    }

    $cleanTerm = trim(preg_replace('/^(divisi|konten|judul)\s*[:\-]?\s*/i', '', $term));
    $effectiveTerm = $cleanTerm !== '' ? $cleanTerm : $term;

    // 1. Division suggestion if matches division name
    $divisiMatches = Divisi::where('nama_divisi', 'like', "%{$effectiveTerm}%")->get();
    foreach ($divisiMatches as $d) {
        $suggestions->push([
            'label' => 'Divisi ' . $d->nama_divisi,
            'sub' => 'Filter galeri berdasarkan divisi ' . $d->nama_divisi,
            'value' => $d->nama_divisi,
        ]);
    }

    // 2. Query konten by judul, deskripsi (konten), and divisi
    $query = Konten::with('divisi');
    if (Auth::user()->role === 'admin') {
        $query->where('divisi_uuid', Auth::user()->divisi_uuid);
    }

    $remaining = max(0, 8 - $suggestions->count());
    if ($remaining > 0) {
        $items = $query->where(function ($q) use ($term, $effectiveTerm) {
            $q->where('judul', 'like', "%{$term}%")
              ->orWhere('deskripsi', 'like', "%{$term}%")
              ->orWhereHas('divisi', function ($divQuery) use ($term, $effectiveTerm) {
                  $divQuery->where('nama_divisi', 'like', "%{$term}%")
                           ->orWhere('nama_divisi', 'like', "%{$effectiveTerm}%");
              });

            if ($effectiveTerm !== $term) {
                $q->orWhere('judul', 'like', "%{$effectiveTerm}%")
                  ->orWhere('deskripsi', 'like', "%{$effectiveTerm}%");
            }
        })
        ->limit($remaining)
        ->get(['uuid', 'judul', 'deskripsi', 'divisi_uuid']);

        foreach ($items as $item) {
            $divName = $item->divisi ? $item->divisi->nama_divisi : 'Umum';
            $plainDesc = trim(strip_tags($item->deskripsi));
            $sub = $divName;

            if (stripos($plainDesc, $effectiveTerm) !== false) {
                $pos = stripos($plainDesc, $effectiveTerm);
                $start = max(0, $pos - 15);
                $snippet = ($start > 0 ? '...' : '') . mb_substr($plainDesc, $start, 50) . '...';
                $sub = $divName . ' • ' . $snippet;
            }

            $suggestions->push([
                'label' => $item->judul,
                'sub' => $sub,
                'value' => $item->judul,
            ]);
        }
    }

    return response()->json($suggestions->take(8)->values()->all());
});

Route::get('/pimpinan/suggestions', [PimpinanAdminController::class, 'suggestions']);

Route::get('/sponsor/suggestions', function(Request $request) {
    if (!Auth::check()) {
        return response()->json([]);
    }
    $term = trim($request->input('term', $request->input('q', '')));
    if (mb_strlen($term) < 2) {
        return response()->json([]);
    }
    $cleanTerm = trim(preg_replace('/^sponsor\s*[:\-]?\s*/i', '', $term));
    $effective = $cleanTerm !== '' ? $cleanTerm : $term;

    $results = Sponsor::where(function($q) use ($term, $effective) {
            $q->where('nama', 'like', "%{$term}%");
            if ($effective !== $term) {
                $q->orWhere('nama', 'like', "%{$effective}%");
            }
        })
        ->limit(8)
        ->get(['uuid', 'nama'])
        ->map(function ($item) {
            return [
                'label' => $item->nama,
                'sub' => 'Mitra Sponsor',
                'value' => $item->nama,
            ];
        });
    return response()->json($results);
});

Route::get('/manage/user/suggestions', function(Request $request) {
    if (!Auth::check() || Auth::user()->role !== 'superadmin') {
        return response()->json([]);
    }
    $term = trim($request->input('term', $request->input('q', '')));
    if (mb_strlen($term) < 2) {
        return response()->json([]);
    }
    $cleanTerm = trim(preg_replace('/^(user|nama|username|email|role|divisi)\s*[:\-]?\s*/i', '', $term));
    $effective = $cleanTerm !== '' ? $cleanTerm : $term;

    $results = User::with('divisi')
        ->where(function($query) use ($term, $effective) {
            $query->where('name', 'like', "%{$term}%")
                  ->orWhere('username', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('role', 'like', "%{$term}%")
                  ->orWhereHas('divisi', function($dq) use ($term, $effective) {
                      $dq->where('nama_divisi', 'like', "%{$term}%")
                         ->orWhere('nama_divisi', 'like', "%{$effective}%");
                  });

            if ($effective !== $term) {
                $query->orWhere('name', 'like', "%{$effective}%")
                      ->orWhere('username', 'like', "%{$effective}%")
                      ->orWhere('email', 'like', "%{$effective}%")
                      ->orWhere('role', 'like', "%{$effective}%");
            }
        })
        ->limit(8)
        ->get(['uuid', 'name', 'email', 'role', 'divisi_uuid'])
        ->map(function ($item) {
            $divName = $item->divisi ? $item->divisi->nama_divisi : 'Tanpa Divisi';
            return [
                'label' => $item->name,
                'sub' => $item->email . ' • ' . ucfirst($item->role) . ' (' . $divName . ')',
                'value' => $item->name,
            ];
        });
    return response()->json($results);
});

Route::get('/manage/event/suggestions', function(Request $request) {
    if (!Auth::check() || Auth::user()->role !== 'superadmin') {
        return response()->json([]);
    }
    $term = trim($request->input('term', $request->input('q', '')));
    if (mb_strlen($term) < 2) {
        return response()->json([]);
    }
    $cleanTerm = trim(preg_replace('/^(acara|kegiatan|agenda)\s*[:\-]?\s*/i', '', $term));
    $effective = $cleanTerm !== '' ? $cleanTerm : $term;

    $results = Calendar::where(function($q) use ($term, $effective) {
            $q->where('event_name', 'like', "%{$term}%")
              ->orWhere('event_date', 'like', "%{$term}%");
            if ($effective !== $term) {
                $q->orWhere('event_name', 'like', "%{$effective}%")
                  ->orWhere('event_date', 'like', "%{$effective}%");
            }
        })
        ->limit(8)
        ->get(['uuid', 'event_name', 'event_date'])
        ->map(function ($item) {
            $dateFormatted = $item->event_date ? \Carbon\Carbon::parse($item->event_date)->translatedFormat('d F Y') : '';
            return [
                'label' => $item->event_name,
                'sub' => $dateFormatted ? 'Pelaksanaan: ' . $dateFormatted : 'Agenda Kegiatan',
                'value' => $item->event_name,
            ];
        });
    return response()->json($results);
});

Route::get('/sponsor', function(Request $request){
    if (!Auth::check()) {
        return redirect('/login');
    }
    $query = Sponsor::query();

    if ($request->filled('search')) {
        $search = trim($request->search);
        $cleanSearch = trim(preg_replace('/^sponsor\s*[:\-]?\s*/i', '', $search));
        $effective = $cleanSearch !== '' ? $cleanSearch : $search;

        $query->where(function($q) use ($search, $effective) {
            $q->where('nama', 'like', "%{$search}%");
            if ($effective !== $search) {
                $q->orWhere('nama', 'like', "%{$effective}%");
            }
        });
    }

    if ($request->input('date_order') === 'oldest') {
        $query->orderBy('nama', 'desc');
    } elseif ($request->input('date_order') === 'name_asc') {
        $query->orderBy('nama', 'asc');
    } else {
        $query->orderBy('nama', 'asc');
    }

    $sponsorPage = $request->input('sponsor-page', 1);
    $allowedPerPage = [2, 5, 10, 20, 50];
    $requestedPerPage = (int) $request->input('per_page');
    $perPage = in_array($requestedPerPage, $allowedPerPage) ? $requestedPerPage : 5;
    $dataSponsor = $query->paginate($perPage, ['*'], 'sponsor-page', $sponsorPage)->withQueryString();

    if ($request->ajax()) {
        if ($request->has('sponsor-page') || $request->has('per_page')) {
            return view('admin.sponsor-table', compact('dataSponsor'))->render();
        }
    } else {
        return view('admin.main.sponsor', compact('dataSponsor'));
    }
});

Route::get('/manage/user', function(Request $request){
    if (!Auth::check()) {
        return redirect('/login');
    }
    $query = User::with('divisi');

    if ($request->filled('search')) {
        $search = trim($request->search);
        $cleanSearch = trim(preg_replace('/^(user|nama|username|email|role|divisi)\s*[:\-]?\s*/i', '', $search));
        $effective = $cleanSearch !== '' ? $cleanSearch : $search;

        $query->where(function($q) use ($search, $effective) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('username', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('role', 'like', "%{$search}%")
              ->orWhereHas('divisi', function($dq) use ($search, $effective) {
                  $dq->where('nama_divisi', 'like', "%{$search}%")
                     ->orWhere('nama_divisi', 'like', "%{$effective}%");
              });

            if ($effective !== $search) {
                $q->orWhere('name', 'like', "%{$effective}%")
                  ->orWhere('username', 'like', "%{$effective}%")
                  ->orWhere('email', 'like', "%{$effective}%")
                  ->orWhere('role', 'like', "%{$effective}%");
            }
        });
    }

    if ($request->filled('role') && in_array($request->role, ['admin', 'superadmin'])) {
        $query->where('role', $request->role);
    }

    $query->orderBy('created_at', 'desc')->orderBy('uuid', 'desc');

    $akunPage = $request->input('account-page', 1);
    $allowedPerPage = [2, 5, 10, 20, 50];
    $requestedPerPage = (int) $request->input('per_page');
    $perPage = in_array($requestedPerPage, $allowedPerPage) ? $requestedPerPage : 5;
    $dataAkun = $query->paginate($perPage, ['*'], 'account-page', $akunPage)->withQueryString();

    if ($request->ajax()) {
        if ($request->has('account-page') || $request->has('per_page')) {
            return view('admin.account-table', compact('dataAkun'))->render();
        }
    } else {
        return view('admin.main.account', compact('dataAkun'));
    }
});

Route::get('/manage/event', function(Request $request){
    if (!Auth::check()) {
        return redirect('/login');
    }
    $query = Calendar::query();

    if ($request->filled('search')) {
        $search = trim($request->search);
        $cleanSearch = trim(preg_replace('/^(acara|kegiatan|agenda)\s*[:\-]?\s*/i', '', $search));
        $effective = $cleanSearch !== '' ? $cleanSearch : $search;

        $query->where(function($q) use ($search, $effective) {
            $q->where('event_name', 'like', "%{$search}%")
              ->orWhere('event_date', 'like', "%{$search}%");
            if ($effective !== $search) {
                $q->orWhere('event_name', 'like', "%{$effective}%")
                  ->orWhere('event_date', 'like', "%{$effective}%");
            }
        });
    }

    if ($request->input('date_order') === 'oldest') {
        $query->orderBy('event_date', 'asc');
    } else {
        $query->orderBy('event_date', 'desc');
    }

    $calendarPage = $request->input('calendar-page', 1);
    $allowedPerPage = [2, 5, 10, 20, 50];
    $requestedPerPage = (int) $request->input('per_page');
    $perPage = in_array($requestedPerPage, $allowedPerPage) ? $requestedPerPage : 5;
    $dataCalendar = $query->paginate($perPage, ['*'], 'calendar-page', $calendarPage)->withQueryString();

    if ($request->ajax()) {
        if ($request->has('calendar-page') || $request->has('per_page')) {
            return view('admin.calendar-table', compact('dataCalendar'))->render();
        }
    } else {
        return view('admin.main.calendar', compact('dataCalendar'));
    }
});

Route::get('/dashboard', function(Request $request) {
    if (Auth::check()) {
        return view('admin.main.dashboard');
    } else {
        return redirect('/login');
    }
})->name('adm.dashboard');

// Pure UUID v4 pattern
$dualIdPattern = '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}';

// ==========================================
// 4. MANAGE ACCOUNT (USER / SUBADMIN) ROUTES
// ==========================================
Route::get('/subadmin', [SubAdminAdminController::class, "index"]);
Route::get('/subadmin/tambah', [SubAdminAdminController::class, "create"])->name('subadmin.create');
Route::get('/manage/user/create', [SubAdminAdminController::class, "create"])->name('user.create');
Route::post('/subadmin/tambah', [SubAdminAdminController::class, "store"]);
Route::post('/manage/user', [SubAdminAdminController::class, "store"])->name('user.store');
Route::get('/subadmin/edit/{id}', [SubAdminAdminController::class, "edit"])->where('id', $dualIdPattern);
Route::get('/manage/user/{id}/edit', [SubAdminAdminController::class, "edit"])->name('user.edit')->where('id', $dualIdPattern);
Route::put('/subadmin/edit/{id}', [SubAdminAdminController::class, "update"])->name('subadmin.update')->where('id', $dualIdPattern);
Route::put('/manage/user/{id}', [SubAdminAdminController::class, "update"])->where('id', $dualIdPattern);
Route::delete('/subadmin/{id}', function($id) {
    $user = User::whereIdentifier($id)->first();
    if ($user) {
        try {
            $user->delete();
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    } else {
        return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
    }
})->name('subadmin.delete')->where('id', $dualIdPattern);
Route::delete('/manage/user/{id}', function($id) {
    $user = User::whereIdentifier($id)->first();
    if ($user) {
        try {
            $user->delete();
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    } else {
        return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
    }
})->where('id', $dualIdPattern);
Route::get('/manage/user/{id}', [SubAdminAdminController::class, 'show'])->name('user.show')->where('id', $dualIdPattern);
Route::get('/subadmin/{id}', [SubAdminAdminController::class, 'show'])->where('id', $dualIdPattern);

// ==========================================
// 3. SPONSOR ROUTES
// ==========================================
Route::get('/sponsor/tambah', [SponsorController::class, 'create'])->name('sponsor.create');
Route::get('/sponsor/create', [SponsorController::class, 'create']);
Route::post('/sponsor', [SponsorController::class, 'store'])->name('sponsor.store');
Route::get('/sponsor/edit/{id}', [SponsorController::class, 'edit'])->name('sponsor.edit')->where('id', $dualIdPattern);
Route::get('/sponsor/{id}/edit', [SponsorController::class, 'edit'])->where('id', $dualIdPattern);
Route::put('/sponsor/{id}', [SponsorController::class, 'update'])->name('sponsor.update')->where('id', $dualIdPattern);
Route::delete('/sponsor/{id}', [SponsorController::class, 'destroy'])->name('sponsor.destroy')->where('id', $dualIdPattern);
Route::get('/sponsor/{id}', [SponsorController::class, 'show'])->name('sponsor.show')->where('id', $dualIdPattern);

// ==========================================
// 2. PIMPINAN MKKS ROUTES (Superadmin only)
// ==========================================
Route::get('/pimpinan', [PimpinanAdminController::class, 'index'])->name('pimpinan.index');
Route::get('/pimpinan/tambah', [PimpinanAdminController::class, 'create'])->name('pimpinan.create');
Route::get('/pimpinan/create', [PimpinanAdminController::class, 'create']);
Route::post('/pimpinan', [PimpinanAdminController::class, 'store'])->name('pimpinan.store');
Route::get('/pimpinan/edit/{id}', [PimpinanAdminController::class, 'edit'])->name('pimpinan.edit')->where('id', $dualIdPattern);
Route::get('/pimpinan/{id}/edit', [PimpinanAdminController::class, 'edit'])->where('id', $dualIdPattern);
Route::put('/pimpinan/{id}', [PimpinanAdminController::class, 'update'])->name('pimpinan.update')->where('id', $dualIdPattern);
Route::delete('/pimpinan/{id}', [PimpinanAdminController::class, 'destroy'])->name('pimpinan.destroy')->where('id', $dualIdPattern);
Route::patch('/pimpinan/{id}/toggle-status', [PimpinanAdminController::class, 'toggleStatus'])->name('pimpinan.toggle')->where('id', $dualIdPattern);
Route::get('/pimpinan/{id}', [PimpinanAdminController::class, 'show'])->name('pimpinan.show')->where('id', $dualIdPattern);

// ==========================================
// 1. GALLERY ROUTES
// ==========================================
Route::get('/galeri-kelola', [GaleriAdminController::class, 'index'])->name('galeri.index');
Route::get('/galeri-kelola/tambah', [GaleriAdminController::class, 'create'])->name('galeri.create');
Route::get('/gallery/create', [GaleriAdminController::class, 'create'])->name('gallery.create');
Route::post('/galeri-kelola', [GaleriAdminController::class, 'store'])->name('galeri.store');
Route::post('/gallery', [GaleriAdminController::class, 'store'])->name('gallery.store');
Route::get('/galeri-kelola/edit/{id}', [GaleriAdminController::class, 'edit'])->name('galeri.edit')->where('id', $dualIdPattern);
Route::get('/gallery/{id}/edit', [GaleriAdminController::class, 'edit'])->name('gallery.edit')->where('id', $dualIdPattern);
Route::put('/galeri-kelola/{id}', [GaleriAdminController::class, 'update'])->name('galeri.update')->where('id', $dualIdPattern);
Route::put('/gallery/{id}', [GaleriAdminController::class, 'update'])->where('id', $dualIdPattern);
Route::delete('/galeri-kelola/{id}', [GaleriAdminController::class, 'destroy'])->name('galeri.destroy')->where('id', $dualIdPattern);
Route::delete('/gallery/{id}', [GaleriAdminController::class, 'destroy'])->where('id', $dualIdPattern);
Route::get('/gallery/{slug}', [GaleriAdminController::class, 'show'])->name('gallery.show');
Route::get('/galeri-kelola/{slug}', [GaleriAdminController::class, 'show']);

// ==========================================
// 5. EVENT SCHEDULE (CALENDAR) ROUTES
// ==========================================
Route::get('/calendar/tambah', function() {
    if (!Auth::check()) return redirect('/login');
    return view('admin.calender.tambahcalendar');
})->name('calendar.create');
Route::get('/manage/event/create', function() {
    if (!Auth::check()) return redirect('/login');
    return view('admin.calender.tambahcalendar');
})->name('event.create');

Route::post('/calendar', function(Request $request) {
    if (!Auth::check()) return redirect('/login');
    $request->validate([
        'name' => 'required|string|max:255',
        'date' => 'required|date',
    ], [
        'name.required' => 'Masukkan nama acara',
        'date.required' => 'Masukkan tanggal acara',
        'date.date' => 'Format tanggal tidak valid',
    ]);

    Calendar::create([
        'event_name' => $request->name,
        'event_date' => $request->date,
    ]);

    return redirect('/manage/event')->with('success', 'Data acara berhasil ditambahkan.');
})->name('calendar.store');
Route::post('/manage/event', function(Request $request) {
    if (!Auth::check()) return redirect('/login');
    $request->validate([
        'name' => 'required|string|max:255',
        'date' => 'required|date',
    ], [
        'name.required' => 'Masukkan nama acara',
        'date.required' => 'Masukkan tanggal acara',
        'date.date' => 'Format tanggal tidak valid',
    ]);

    Calendar::create([
        'event_name' => $request->name,
        'event_date' => $request->date,
    ]);

    return redirect('/manage/event')->with('success', 'Data acara berhasil ditambahkan.');
});

Route::get('/calendar/edit/{id}', function($id) {
    if (!Auth::check()) return redirect('/login');
    $calendar = Calendar::whereIdentifier($id)->firstOrFail();
    return view('admin.calender.editcalendar', compact('calendar'));
})->name('calendar.edit')->where('id', $dualIdPattern);
Route::get('/manage/event/{id}/edit', function($id) {
    if (!Auth::check()) return redirect('/login');
    $calendar = Calendar::whereIdentifier($id)->firstOrFail();
    return view('admin.calender.editcalendar', compact('calendar'));
})->name('event.edit')->where('id', $dualIdPattern);

Route::put('/calendar/{id}', function(Request $request, $id) {
    if (!Auth::check()) return redirect('/login');
    $request->validate([
        'name' => 'required|string|max:255',
        'date' => 'required|date',
    ], [
        'name.required' => 'Masukkan nama acara',
        'date.required' => 'Masukkan tanggal acara',
        'date.date' => 'Format tanggal tidak valid',
    ]);

    $calendar = Calendar::whereIdentifier($id)->firstOrFail();
    $calendar->update([
        'event_name' => $request->name,
        'event_date' => $request->date,
    ]);

    return redirect('/manage/event')->with('success', 'Data acara berhasil diperbarui.');
})->name('calendar.update')->where('id', $dualIdPattern);
Route::put('/manage/event/{id}', function(Request $request, $id) {
    if (!Auth::check()) return redirect('/login');
    $request->validate([
        'name' => 'required|string|max:255',
        'date' => 'required|date',
    ], [
        'name.required' => 'Masukkan nama acara',
        'date.required' => 'Masukkan tanggal acara',
        'date.date' => 'Format tanggal tidak valid',
    ]);

    $calendar = Calendar::whereIdentifier($id)->firstOrFail();
    $calendar->update([
        'event_name' => $request->name,
        'event_date' => $request->date,
    ]);

    return redirect('/manage/event')->with('success', 'Data acara berhasil diperbarui.');
})->where('id', $dualIdPattern);

Route::delete('/calendar/{id}', function($id) {
    if (!Auth::check()) return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
    $calendar = Calendar::whereIdentifier($id)->first();
    if ($calendar) {
        try {
            $calendar->delete();
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Kesalahan Teknis, Coba Lagi.'], 500);
        }
    } else {
        return response()->json(['status' => 'error', 'message' => 'Kalender tidak ditemukan!'], 404);
    }
})->name('calendar.delete')->where('id', $dualIdPattern);
Route::delete('/manage/event/{id}', function($id) {
    if (!Auth::check()) return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
    $calendar = Calendar::whereIdentifier($id)->first();
    if ($calendar) {
        try {
            $calendar->delete();
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Kesalahan Teknis, Coba Lagi.'], 500);
        }
    } else {
        return response()->json(['status' => 'error', 'message' => 'Kalender tidak ditemukan!'], 404);
    }
})->where('id', $dualIdPattern);

Route::get('/manage/event/{id}', function($id) {
    if (!Auth::check()) return redirect('/login');
    $calendar = Calendar::whereIdentifier($id)->firstOrFail();
    return view('admin.calender.show', compact('calendar'));
})->name('calendar.show')->where('id', $dualIdPattern);
Route::get('/calendar/{id}', function($id) {
    if (!Auth::check()) return redirect('/login');
    $calendar = Calendar::whereIdentifier($id)->firstOrFail();
    return view('admin.calender.show', compact('calendar'));
})->where('id', $dualIdPattern);

