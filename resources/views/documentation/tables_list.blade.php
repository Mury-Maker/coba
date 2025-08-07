{{-- usecase_index.blade.php --}}
    <div class="max-w-7xl mx-auto px-2 sm:px-2 lg:px-2">
        <div class="bg-white rounded-lg shadow-md p-6">

                <div class="judul-halaman">
                    <h1 id="main-content-title"> {!! ucfirst(Str::headline($currentPage)) !!}</h1>
                </div>

        {{-- Jika file sql ditemukan  --}}
            @if($sqlFile)
            <div class="sql">
            
            <p>{{$menu_id}}</p>
            <h2 class="text-2xl font-bold mb-4 text-gray-800">File SQL Tersedia</h2>
            <p>Nama File Saat ini: 
                
                <strong>
                    <a href="{{ $sqlPath }}">
                    {{ $sqlFile->file_name }}
                </a>
                </strong>

                <p>Tekan tombol berikut untuk menampilkan ERD</p>
                <form action="{{ route('sql.parse', ['navmenuId' => $menu_id]) }}" method="POST" class="mt-4">
                    @csrf
                    <input type="hidden" name="navmenu_id" value="{{ $menu_id }}">
                    <button type="submit" class="focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">
                        Parse Sql
                    </button>
                </form>
                <hr style="margin-bottom: 12px">
            </div>

            <div class="erd">
                <div class="judul-halaman">
                <h2 class="text-lg font-bold mb-4" style="flex-grow: 1">Diagram ERD (GoJS)</h2>
                    <div class="btn-fullscreen">
                        <button onclick="toggleFullScreen()" style="margin-bottom: 10px;">
                            <i class="fa-solid fa-expand"></i>
                            Fullscreen
                        </button>
                    </div>
                </div>

                <div id="myDiagramDiv" style="width:100%; height:600px; border:1px solid black; padding:28px;">
                    <div class="btn-fullscreen">
                        
                    </div>
                </div>

            </div>

            <hr style="margin-bottom: 12px;">

            <div class="deleteSql">
                <p>Hapus File</p>
                <form action="{{ route('sql.delete', ['navmenuId' => $menu_id]) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus file dan semua datanya?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">Hapus File & Data</button>
                </form>
                <hr style="margin-bottom: 12px">
            </div>

            <div class="updateSql">
                <p>Ganti file:</p>
                <form action="{{ route('sql.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" name="navmenu_id" value="{{ $menu_id }}" />
                    

                    <div class="mb-4">
                        <input type="file" name="sql_file" accept=".sql" required class="block w-full border rounded p-2" />
                    </div>

                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Upload</button>
                </form>
            </div>

        </div>

        {{-- Jika file sql tidak ditemukan  --}}
            @else
            <div class="nosql">
            <p>Menu ID: {{ $menu_id }}</p>
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Belum ada file SQL</h2>

            <form action="{{ route('sql.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="navmenu_id" value="{{ $menu_id }}" />

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

        const erdData = @json(session('erd') ?? $erd ?? ['nodes'=>[], 'links'=>[]]);

        function initGoJS() {
            const $ = go.GraphObject.make;

            const myDiagram = $(go.Diagram, "myDiagramDiv", {
                initialContentAlignment: go.Spot.Left,
                layout: $(go.ForceDirectedLayout),
                "undoManager.isEnabled": true,
                "linkingTool.direction": go.LinkingTool.ForwardsOnly
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

        // Jalankan GoJS setelah halaman dimuat
        window.addEventListener('DOMContentLoaded', initGoJS);
    </script>


