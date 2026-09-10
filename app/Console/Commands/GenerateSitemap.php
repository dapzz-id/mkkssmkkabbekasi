<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'generate:sitemap';
    protected $description = 'Generate the sitemap.xml file';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Sitemap::create()
            ->add(Url::create('/')
                ->setLastModificationDate(now())
                ->setChangeFrequency('daily')
                ->setPriority(1.0))
            ->add(Url::create('/divisi')
                ->setLastModificationDate(now())
                ->setChangeFrequency('daily')
                ->setPriority(0.9))
            ->add(Url::create('/galeri')
                ->setLastModificationDate(now())
                ->setChangeFrequency('daily')
                ->setPriority(0.9))
            ->add(Url::create('/jadwal')
                ->setLastModificationDate(now())
                ->setChangeFrequency('daily')
                ->setPriority(0.9))
            ->add(Url::create('/login')
                ->setLastModificationDate(now())
                ->setChangeFrequency('weekly')
                ->setPriority(0.5))
            ->add(Url::create('/dashboard')
                ->setLastModificationDate(now())
                ->setChangeFrequency('weekly')
                ->setPriority(0.7))
            ->add(Url::create('/konten')
                ->setLastModificationDate(now())
                ->setChangeFrequency('weekly')
                ->setPriority(0.8))
            ->add(Url::create('/sponsor')
                ->setLastModificationDate(now())
                ->setChangeFrequency('weekly')
                ->setPriority(0.7))
            ->add(Url::create('/gallery')
                ->setLastModificationDate(now())
                ->setChangeFrequency('weekly')
                ->setPriority(0.7))
            ->add(Url::create('/manage/user')
                ->setLastModificationDate(now())
                ->setChangeFrequency('weekly')
                ->setPriority(0.7))
            ->add(Url::create('/manage/event')
                ->setLastModificationDate(now())
                ->setChangeFrequency('weekly')
                ->setPriority(0.7))
            ->add(Url::create('/galeri-kelola')
                ->setLastModificationDate(now())
                ->setChangeFrequency('weekly')
                ->setPriority(0.6))
            ->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully!');
    }
}