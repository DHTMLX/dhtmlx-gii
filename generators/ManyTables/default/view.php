<?php
echo "<?php\n";
?>
use DHTMLX\Asset\DHTMLXAsset;
DHTMLXAsset::register($this);
<?= "?>" ?>

<div id="layout" style="position: absolute; width:100%; height: 100%;"> </div>
<script type="text/javascript" charset="utf-8">


    dhtmlxEvent(window,"load",function(){

        var layout = new dhtmlXLayoutObject({
            parent: "layout",
            pattern: "3T"   // <-- pattern
        });

        var header = layout.cells('a');
        header.setHeight(50);
        var nav = layout.cells('b');
        var table = layout.cells('c');

        nav.setText('Navigation');
        nav.setWidth(200);

        table.setText("<?=ucfirst($tableName)?>");

        createTree(nav);
        createGrid(table);


    });


    function createGrid(cell) {
        var mygrid = cell.attachGrid();
        mygrid.setHeader("<?=$headers?>");
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
        var mytree = cell.attachTree();

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

        mytree.selectItem("<?=$controllerName?>/table");

        //Redirect on click
        mytree.attachEvent("onClick", function(id){
            document.location = "<?= Yii::$app->homeUrl?>"+id;
        });
    }



</script>