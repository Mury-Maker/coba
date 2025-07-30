{{-- resources/views/partials/_modals/_image_viewer_manual_modal.blade.php --}}

<div id="myModal" class="modal"> {{-- Kelas 'modal' dari app.css --}}
    <span class="close-w3 cursor" onclick="window.closeModal()">&times;</span>

    <div class="modal-content-w3"> {{-- Konten modal W3S --}}
        {{-- Area Slides Utama --}}
        <div id="slidesContainer">
            {{-- Slides akan diisi dinamis di sini oleh JS --}}
            {{-- Ini adalah placeholder, elemen mySlides akan dibuat dan disisipkan di sini --}}
        </div>

        {{-- Area Caption --}}
        <div class="caption-container">
            <p id="caption"></p>
        </div>

        {{-- Area Thumbnails --}}
        <div id="thumbnailRow" class="row">
            {{-- Thumbnails akan diisi dinamis di sini oleh JS --}}
        </div>
    </div>

    {{-- Tombol Navigasi (anak langsung dari #myModal, bukan modal-content-w3) --}}
    <a class="prev-w3" onclick="window.plusSlides(-1)">&#10094;</a>
    <a class="next-w3" onclick="window.plusSlides(1)">&#10095;</a>
</div>
