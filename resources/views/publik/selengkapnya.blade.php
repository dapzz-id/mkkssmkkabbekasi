@extends('publik.layouts.main')

@section('container')
<div class="selengkapnya-section">
    <div class="container container-spacing">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- First Content Section -->
                <div class="mb-5">
                    <div class="selengkapnya-card">
                        <div class="row g-0">
                            <div class="col-md-6">
                                <div class="selengkapnya-image-wrapper">
                                    <img src="{{ asset('img/kiri.jpg') }}"
                                         alt="Profile Image 1"
                                         class="selengkapnya-image">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="selengkapnya-content">
                                    <h2 class="content-title">Visi Kami</h2>
                                    <p class="content-text">
                                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Odio consequatur praesentium voluptatibus, illum maiores placeat ut laudantium quis quia possimus reiciendis non debitis! Nesciunt perspiciatis doloremque expedita veniam eligendi ratione.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Second Content Section -->
                <div class="mb-5">
                    <div class="selengkapnya-card">
                        <div class="row g-0 flex-md-row-reverse">
                            <div class="col-md-6">
                                <div class="selengkapnya-image-wrapper">
                                    <img src="{{ asset('img/kanan.jpg') }}"
                                         alt="Profile Image 2"
                                         class="selengkapnya-image">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="selengkapnya-content">
                                    <h2 class="content-title">Misi Kami</h2>
                                    <p class="content-text">
                                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Temporibus consequatur officia dicta qui, commodi placeat eligendi pariatur, laborum dolores magni sit facere incidunt. Temporibus reprehenderit sapiente, explicabo doloremque quas officiis.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
