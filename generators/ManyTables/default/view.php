<div id="layout" style="width:auto; height:800px;"> </div>
<script type="text/javascript" charset="utf-8">

    function createGrid(cell) {
        mygrid = cell.attachGrid();
        mygrid.setHeader("<?=$fields?>");
        mygrid.init();

        mygrid.load("./table_data");

        var myDataProcessor = new dataProcessor("./table_data"); //lock feed url
        myDataProcessor.init(mygrid); //link dataprocessor to the grid

        var toolbar = cell.attachToolbar({
            items:[
                {id: "new", type: "button", text: "Add new row"},
                {id: "delete", type: "button", text: "Remove" }
            ]
        });

        toolbar.attachEvent("onClick", function(id){
            switch (id) {
                case 'new':
                    var id=mygrid.uid(); mygrid.addRow(id,'',0); mygrid.showRow(id);
                    break;
                case 'delete':
                    mygrid.deleteSelectedItem()
                    break;
            }
        });
    }

    function createTree(cell) {
        mytree = cell.attachTree();

        mytree.setImagesPath("/dhtmlx/codebase/imgs/dhxtree_skyblue/");
        mytree.loadJSONObject(
            {id:0, item:[
                {id:2, text:"Tables", item:[
                    <?php foreach ($tables as $table): ?>
                    {id:"<?=$table['url']?>", text:"<?=$table['name']?>"}<?=$table['comma']?>
                    <?php endforeach;?>
                ]
                }
            ]
            }
        );

        mytree.openAllItems(0);
    }

    dhtmlxEvent(window,"load",function(){

        var layout = new dhtmlXLayoutObject({
            parent: "layout",
            pattern: "2U"   // <-- pattern
        });

        var nav = layout.cells('a');
        var table = layout.cells('b');

        nav.setText('Navigation');
        nav.setWidth(200);
        table.setText("<?=$tableName?>");

        createTree(nav);
        createGrid(table);

        //Redirect on click
        mytree.attachEvent("onDblClick", function(id){
            document.location = id;
        });


    });

</script>