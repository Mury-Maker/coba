{{-- usecase_index.blade.php --}}
    <div class="max-w-7xl mx-auto px-2 sm:px-2 lg:px-2">
        <div class="bg-white rounded-lg shadow-md p-6">

                <div class="judul-halaman">
                    <h1 id="main-content-title"> {!! ucfirst(Str::headline($currentPage)) !!}</h1>
                </div>

        {{-- Jika file sql ditemukan  --}}
            @if($sqlFile)
            <div class="sql">
            
            <h2 class="text-2xl font-bold mb-4 text-gray-800">File SQL Tersedia</h2>

            <p>Nama File Saat ini: <br></p>
                
                <strong>
            <div class="deleteSql">  
                <a title="Download File .sql" href="{{ asset('storage/sql_files/' . $sqlFile->file_name) }}">
                    {{ $sqlFile->file_name }}
                </a>

                <form action="{{ route('sql.delete', ['navmenuId' => $catID]) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus file dan semua datanya?')">
                    @csrf
                    @method('DELETE')
                    <button title="Hapus File dan Data" type="submit" class=""><i class="fa-solid fa-trash" style="color: #ff0000;"></i></button>
                </form>
            </div>
                </strong>

                <p>Tekan tombol berikut untuk menampilkan ERD</p>
                <form action="{{ route('erd.generate', ['categoryId' => $catID]) }}" method="POST" class="mt-4">
                    @csrf
                    <input type="hidden" name="category_id" value="{{ $catID }}">
                    <button type="submit" class="focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">
                        Generate ERD
                    </button>
                </form>
                <hr style="margin-bottom: 12px">
            </div>



            <div class="updateSql">
                <h2 class="text-2xl font-bold mb-4 text-gray-800" ><button onclick="toggleFileUpdate()">Ganti file:</button></h2>
                <div class="form-update-erd hidden">
                    <form action="{{ route('sql.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" name="category_id" value="{{ $catID }}" />
                    

                    <div class="mb-4">
                        <input type="file" name="sql_file" accept=".sql" required class="block w-full border rounded p-2" />
                    </div>

                    <button type="submit" class="focus:outline-none text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Simpan</button>
                    <button onclick="window.location.reload();" class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">Batal</button>
                </form>
                </div>
                <hr style="margin-bottom: 12px; ">
            </div>

            <div class="erd">
                <div class="judul-halaman">
                <h2 class="text-2xl font-bold mb-4 text-gray-800" style="flex-grow: 1">Diagram ERD (GoJS)</h2>
                    <div class="btn-fullscreen">
                        <button onclick="toggleFullScreen()">
                            <i class="fa-solid fa-expand"></i>
                            Fullscreen
                        </button>
                    </div>

                    <div class="dropdown-download">
                    <button onclick="toggleDropdownDownloads()" class="btn-download">Downloads <i class="fa-regular fa-image"></i> </button>
                        <div id="myDropdown" class="dropdown-content">
                            <button onclick="makePNG()">PNG</button>
                            <button onclick="makeSvg()">SVG</button>
                            <button onclick="makeImage()">JPEG</button>
                        </div>
                    </div>

                </div>

                

                <div id="myDiagramDiv" style="width:100%; height:600px; border:1px solid black; padding:28px;">
                    
                </div>

            </div>

            <hr style="margin-bottom: 12px;">




        </div>

        {{-- Jika file sql tidak ditemukan  --}}
            @else
            <div class="nosql">
            <p>Menu ID: {{ $catID }}</p>
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Belum ada file SQL</h2>

            <form action="{{ route('sql.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="category_id" value="{{ $catID }}" />

                <label class="files_sql" for="sql_file">Silahkan Klik Disini Untuk Memilih File SQL (Hanya menerima file dari hasil export HeidiSQL)</label>
                <div class="mb-4">
                    <input id="sql_file" type="file" name="sql_file" accept=".sql" required class="block w-full border rounded p-2" />
                </div>

                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Upload</button>
            </form>
            </div>

            @endif

        </div>
    </div>


    {{-- Script Untuk Generate ERD --}}
    <script src="https://unpkg.com/gojs/release/go-debug.js"></script>
    <script>

        function toggleFullScreen() {
            const elem = document.getElementById("myDiagramDiv");
            if (!document.fullscreenElement) {
                elem.requestFullscreen().catch(err => {
                    alert(`Gagal masuk fullscreen: ${err.message}`);
                });
            } else {
                document.exitFullscreen();
            }
        }

        function toggleFileUpdate() {
            document.querySelectorAll('.form-update-erd').forEach(el => {
                el.classList.toggle('hidden');
                el.classList.toggle('show');
            });
        }



        const erdData = @json(session('erd') ?? $erd ?? ['nodes'=>[], 'links'=>[]]);

        function initGoJS() {
            let $ = go.GraphObject.make;

            myDiagram = $(go.Diagram, "myDiagramDiv", {
                initialContentAlignment: go.Spot.Left,
                layout: $(go.ForceDirectedLayout),
                "undoManager.isEnabled": true,
                "linkingTool.direction": go.LinkingTool.ForwardsOnly,
                padding: 100
            });
                myDiagram.nodeTemplate =
                $(go.Node, "Auto", {
                    selectionAdorned: true,
                    resizable: false,
                    resizeObjectName: "TABLE",
                    layoutConditions: go.LayoutConditions.Standard & ~go.LayoutConditions.NodeSized,
                    fromSpot: go.Spot.LeftRightSides,
                    toSpot: go.Spot.LeftRightSides,
                },
                    $(go.Shape, "Rectangle", {
                        fill: "#E3F2FD",
                        stroke: "#039BE5",
                        strokeWidth: 2
                    }),
                    $(go.Panel, "Table", {
                        name: "TABLE", // penting agar resizeObjectName tahu panel ini yang dirubah
                        margin: 4,
                        defaultAlignment: go.Spot.Left,
                        stretch: go.GraphObject.Horizontal,
                        
                    },

                        // Header Nama Tabel
                        $(go.TextBlock,
                            {
                                row: 0, column: 0, columnSpan: 3,
                                font: "bold 14px sans-serif",
                                margin: new go.Margin(4, 4, 10, 4),
                                stroke: "#0D47A1",
                                alignment: go.Spot.Center
                            },
                            new go.Binding("text", "key")
                        ),

                        // Panel Daftar Kolom
                        $(go.Panel, "Vertical",
                            {
                                row: 1, column: 0, columnSpan: 3,
                                name: "FIELDS",
                                itemTemplate:
                                    $(go.Panel, "Table",
                                        {
                                            defaultColumnSeparatorStroke: "#000000", // garis vertikal antar kolom
                                            defaultColumnSeparatorStrokeWidth: 0.5,
                                            defaultRowSeparatorStroke: "#E0E0E0",    // garis horizontal antar baris
                                            defaultRowSeparatorStrokeWidth: 1,
                                        },
                                        // Nama kolom
                                        $(go.TextBlock,
                                            {
                                                column: 0,
                                                margin: new go.Margin(2, 4),
                                                font: "16px monospace",
                                                stroke: "#263238",
                                                width: 125,
                                                height: 28,
                                                text: "verticalAlignment: Center",
                                                wrap: go.Wrap.Fit,
                                                alignment: go.Spot.Left,
                                                stretch: go.GraphObject.Horizontal
                                            },
                                            new go.Binding("text", "name")
                                        ),
                                        // Tipe kolom
                                        $(go.TextBlock,
                                            {
                                                column: 1,
                                                margin: new go.Margin(2, 4),
                                                font: "16px monospace",
                                                stroke: "#607D8B",
                                                width: 125,
                                                height: 28,
                                                text: "verticalAlignment: Center",
                                                wrap: go.Wrap.Fit,
                                                alignment: go.Spot.Left,
                                                stretch: go.GraphObject.Horizontal
                                            },
                                            new go.Binding("text", "type")
                                        ),
                                        // Suffix (PK, FK, UK)
                                        $(go.TextBlock,
                                            {
                                                column: 2,
                                                margin: new go.Margin(2, 4),
                                                font: "16px monospace",
                                                stroke: "#D81B60",
                                                width: 50,
                                                height: 28,
                                                text: "verticalAlignment: Center",
                                                wrap: go.Wrap.Fit,
                                                alignment: go.Spot.Left,
                                                stretch: go.GraphObject.Horizontal
                                            },
                                            new go.Binding("text", "suffix")
                                        )
                                    )

                            },
                            new go.Binding("itemArray", "fields")
                        )
                    )
                );


            // Link Template — support multi-links
            myDiagram.linkTemplate =
                $(go.Link,
                    {
                        routing: go.Link.AvoidsNodes,
                        curve: go.Link.JumpGap,
                        corner: 5,
                        toShortLength: 4,
                        relinkableFrom: true,
                        relinkableTo: true
                    },
                    new go.Binding("points").makeTwoWay(),
                    $(go.Shape, { strokeWidth: 1.5 }),
                    $(go.Shape, { toArrow: "OpenTriangle", stroke: null, fill: "#444" }),
                    $(go.TextBlock,
                        {
                            segmentOffset: new go.Point(0, -10),
                            font: "10pt sans-serif",
                            stroke: "#555"
                        },
                        new go.Binding("text", "relationship")
                    )
                );

            // Enable multiple links between same nodes (important!)
            myDiagram.toolManager.linkingTool.duplicateLinks = true;
            myDiagram.toolManager.relinkingTool.duplicateLinks = true;

            myDiagram.model = new go.GraphLinksModel(erdData.nodes, erdData.links);

            // Allow multiple links between the same nodes with different keys
            myDiagram.model.linkKeyProperty = "key";
        }
        
        function toggleDropdownDownloads() {
                document.getElementById("myDropdown").classList.toggle("showDownloads");
        }

            // Close the dropdown menu if the user clicks outside of it
        window.onclick = function(event) {
            if (!event.target.matches('.btn-download')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                var i;
                for (i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                        if (openDropdown.classList.contains('showDownloads')) {
                            openDropdown.classList.remove('showDownloads');
                        }
                    }
                }
            }

        function pngCallback(blob) {
            var url = window.URL.createObjectURL(blob);

            var name = '{{ $sqlFile->file_name ?? "" }}';
            var category = '{{ $currentCategory ?? "" }}';
            name  = name.replace('.sql','');
            console.log(name);
            console.log(category);
            var filename = `${category}-erd-${name}.png`;

            var a = document.createElement('a');
            a.style = 'display: none';
            a.href = url;
            a.download = filename;

            // IE 11
            if (window.navigator.msSaveBlob !== undefined) {
            window.navigator.msSaveBlob(blob, filename);
            return;
            }

            document.body.appendChild(a);
            requestAnimationFrame(() => {
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            });
        }

        function jpegCallback(blob) {
            var url = window.URL.createObjectURL(blob);

            var name = '{{ $sqlFile->file_name ?? "" }}';
            name  = name.replace('.sql','');
            var category = '{{ $currentCategory ?? "" }}';

            console.log(name)

            var filename = `${category}-erd-${name}.jpeg`;

            var a = document.createElement('a');
            a.style = 'display: none';
            a.href = url;
            a.download = filename;

            // IE 11
            if (window.navigator.msSaveBlob !== undefined) {
            window.navigator.msSaveBlob(blob, filename);
            return;
            }

            document.body.appendChild(a);
            requestAnimationFrame(() => {
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            });
        }

        function makePNG() {
            var blob = myDiagram.makeImageData({ 
                                background: 'white', 
                                returnType: 'blob',
                                scale: 1, 
                                maxSize: new go.Size(3000, 3000),
                                padding: 100,
                                callback: pngCallback 
            });
        }

        function makeImage() {
            myDiagram.makeImageData({
            maxSize: new go.Size(3000, 3000),
            returnType: 'blob',
            scale: 1,
            background: "white",
            type: "image/jpeg",
            callback: jpegCallback,
            padding: 100
            });
        }

        function svgCallback(blob) {
            var url = window.URL.createObjectURL(blob);


            var name = '{{ $sqlFile->file_name ?? "" }}';
            name  = name.replace('.sql','');
            var category = '{{ $currentCategory ?? "" }}';

            console.log(name)

            var filename = `${category}-erd-${name}.svg`;

            var a = document.createElement('a');
            a.style = 'display: none';
            a.href = url;
            a.download = filename;

            // IE 11
            if (window.navigator.msSaveBlob !== undefined) {
            window.navigator.msSaveBlob(blob, filename);
            return;
            }

            document.body.appendChild(a);
            requestAnimationFrame(() => {
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            });
        }

        function makeSvg() {
            var svg = myDiagram.makeSvg({ 
                scale: 1, 
                background: 'white',
                padding: 100
            });
            var svgstr = new XMLSerializer().serializeToString(svg);
            var blob = new Blob([svgstr], { type: 'image/svg+xml' });
            svgCallback(blob);
        }
        

        // Jalankan GoJS setelah halaman dimuat
        window.addEventListener('DOMContentLoaded', initGoJS);

    </script>

    


