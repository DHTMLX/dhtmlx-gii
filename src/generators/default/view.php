<?php echo "<?php\n"; ?>
use app\assets\DHTMLXAsset;
DHTMLXAsset::register($this);
<?php echo "?>"; ?>

<div id="grid_here" style="width:auto; height:800px;"> </div>
<p><a href="javascript:void(0)" onclick="var id=mygrid.uid(); mygrid.addRow(id,'',0); mygrid.showRow(id);">Add row</a></p>
<p><a href="javascript:void(0)" onclick="mygrid.deleteSelectedItem()">Remove Selected Row</a></p>
<script type="text/javascript" charset="utf-8">
    mygrid = new dhtmlXGridObject('grid_here');
    mygrid.setHeader("<?=$fields?>");
    mygrid.init();

    mygrid.load("./<?=$generator->actionName?>_data");

    myDataProcessor = new dataProcessor("./<?=$generator->actionName?>_data"); //lock feed url
    myDataProcessor.init(mygrid); //link dataprocessor to the grid
</script>