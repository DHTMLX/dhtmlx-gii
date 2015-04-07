<?php
echo "<?php\n";
?>
use DHTMLX\Asset\DHTMLXAsset;
DHTMLXAsset::register($this);
<?= "?>" ?>
<div id="layout" style="width:auto; height:800px;"> </div>
<!--<p><a href="javascript:void(0)" onclick="var id=mygrid.uid(); mygrid.addRow(id,'',0); mygrid.showRow(id);">Add row</a></p>-->
<!--<p><a href="javascript:void(0)" onclick="mygrid.deleteSelectedItem()">Remove Selected Row</a></p>-->
<script type="text/javascript" charset="utf-8">

    dhtmlxEvent(window,"load",function(){
        var layout = new dhtmlXLayoutObject({
            parent: "layout",
            pattern: "1C"   // <-- pattern
        });

        mygrid = layout.cells('a').attachGrid();

        layout.cells('a').setText('<?=$generator->tableName?>');
        mygrid.setHeader("<?=$fields?>");
        mygrid.init();

        mygrid.load("./<?=$generator->actionName?>_data");

        var myDataProcessor = new dataProcessor("./<?=$generator->actionName?>_data"); //lock feed url
        myDataProcessor.init(mygrid); //link dataprocessor to the grid


        var toolbar = layout.cells('a').attachToolbar({
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
    });

</script>