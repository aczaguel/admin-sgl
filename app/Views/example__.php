<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php foreach($output->css_files as $file): ?>
        <link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
    <?php endforeach; ?>



   

<style type='text/css'>
body
{
 font-family: Arial;
 font-size: 14px;
}
</style>
</head>
<body>
<!-- Beginning header -->
 <div>
     <a href='<?php echo site_url('example/customers')?>'>Customers</a>
 </div>
<!-- End of header-->

 <!-- Beginning of main content -->
 <div style='height:20px;'></div> 
 <div style='padding: 10px;'>

     <?php print_r( $output ); ?>

 </div>
 <!-- End of main content -->

<!-- Beginning footer -->

<!-- End of Footer -->



        <div style="padding: 20px 10px;">
            <?php print_r($output->output); ?>
        </div>
        <?php foreach($output->js_files as $file): ?>
            <script src="<?php echo $file; ?>"></script>
        <?php endforeach; ?>
</body>

</html>