<?php
header('Access-Control-Allow-Origin: *');
$pdf=$_GET['file'];
if(empty($pdf)) exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>PDF Viewer</title>
    <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <link href="css/dflip.min.css" rel="stylesheet" type="text/css">
    <link href="css/themify-icons.min.css" rel="stylesheet" type="text/css">
    <style>
        html,body {
            margin:0;
            padding:0;
        }
        #pdf_container {
            height: 100vh;
        }
    </style>
</head>
<body>
<div id="pdf_container"></div>
<script src="js/libs/jquery.min.js" type="text/javascript"></script>
<script src="js/dflip.min.js" type="text/javascript"></script>
<script>
    jQuery(function(){
        var width = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0)
        var height = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0)
        if(height>width) {
            var page_mode = DFLIP.PAGE_MODE.SINGLE;
        } else {
            var page_mode = DFLIP.PAGE_MODE.DOUBLE;
        }
        var source_pdf = "<?php echo $pdf; ?>";
        var option_pdf = {webgl:true,pageMode:page_mode,singlePageMode:DFLIP.SINGLE_PAGE_MODE.BOOKLET,transparent:true,enableDownload:false,hideControls:'download,pageMode,startPage,endPage,sound',allControls: "altPrev,pageNumber,altNext,play,outline,thumbnail,zoomIn,zoomOut,fullScreen"};
        $("#pdf_container").flipBook(source_pdf,option_pdf);
    });
</script>
</body>
</html>
