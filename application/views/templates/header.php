<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="description" content="<?php echo $site_description; ?>" />
    <title><?php echo $title; ?> | <?php echo $site_title ;?></title>
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/style.css">
    
	<script src='http://code.jquery.com/jquery-1.10.2.js'></script>
   
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }
	</style>
	<style>
#content {
    width:700px;
    margin:30px auto;
}
.thermometer {
    float: left;
    margin:0 150px;
}
.thermometer {
    width:70px;
    height:300px;
    position: relative;
    background: #ddd;
    border:1px solid #aaa;
    -webkit-border-radius: 12px;
    -moz-border-radius: 12px;
    -ms-border-radius: 12px;
    -o-border-radius: 12px;
    border-radius: 12px;
    -webkit-box-shadow: 1px 1px 4px #999, 5px 0 20px #999;
    -moz-box-shadow: 1px 1px 4px #999, 5px 0 20px #999;
    -ms-box-shadow: 1px 1px 4px #999, 5px 0 20px #999;
    -o-box-shadow: 1px 1px 4px #999, 5px 0 20px #999;
    box-shadow: 1px 1px 4px #999, 5px 0 20px #999;
}
.thermometer .track {
    height:280px;
    top:10px;
    width:20px;
    border: 1px solid #aaa;
    position: relative;
    margin:0 auto;
    background: rgb(255, 255, 255);
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, rgb(0, 0, 0)), color-stop(1%, rgb(255, 255, 255)));
    background: -webkit-linear-gradient(top, rgb(0, 0, 0) 0%, rgb(255, 255, 255) 10%);
    background: -o-linear-gradient(top, rgb(0, 0, 0) 0%, rgb(255, 255, 255) 10%);
    background: -ms-linear-gradient(top, rgb(0, 0, 0) 0%, rgb(255, 255, 255) 10%);
    background: -moz-linear-gradient(top, rgb(0, 0, 0) 0%, rgb(255, 255, 255) 10%);
    background: linear-gradient(to bottom, rgb(0, 0, 0) 0%, rgb(255, 255, 255) 10%);
    background-position: 0 -1px;
    background-size: 100% 5%;
}
.thermometer .progress {
    height:0%;
    width:100%;
    background: rgb(255, 0, 0);
    background: rgba(255, 0, 0, 0.75);
    bottom:0;
    left:0;
}
.thermometer .goal {
    position:absolute;
    top:0;
}
.thermometer .amount, .therm-text {
    display: inline-block;
    padding:0 5px 0 60px;
    border-top:1px solid black;
    font-family: Trebuchet MS;
    font-weight: bold;
    color:#333;
}
.thermometer .progress .amount {
    padding:0 60px 0 5px;
    position: absolute;
    border-top:1px solid #333;
    color:#333;
    right:0;
}
.thermometer.horizontal {
    margin:100px auto;
}
.thermometer.horizontal {
    width:350px;
    height:70px;
}
.thermometer.horizontal .track {
    width:90%;
    left:0;
    height:20px;
    margin:14px auto;
    background: -webkit-gradient(linear, left top, right top, color-stop(0%, rgb(0, 0, 0)), color-stop(1%, rgb(255, 255, 255)));
    background: -webkit-linear-gradient(left, rgb(0, 0, 0) 0%, rgb(255, 255, 255) 10%);
    background: -o-linear-gradient(left, rgb(0, 0, 0) 0%, rgb(255, 255, 255) 10%);
    background: -ms-linear-gradient(left, rgb(0, 0, 0) 0%, rgb(255, 255, 255) 10%);
    background: -moz-linear-gradient(left, rgb(0, 0, 0) 0%, rgb(255, 255, 255) 10%);
    background: linear-gradient(to right, rgb(0, 0, 0) 0%, rgb(255, 255, 255) 10%);
    background-size: 5% 100%;
}
.thermometer.horizontal .progress {
    height:100%;
    width:0%;
}
.thermometer.horizontal .goal {
    left:100%;
    height:100%;
}
.thermometer.horizontal .amount {
    bottom:0;
    position: absolute;
    padding:0 5px 50px 5px;
    border-top:0;
    border-left:1px solid black;
}
.thermometer.horizontal .progress .amount {
    border-left:0;
    border-top:0;
    border-right:1px solid #333;
}		
	</style>
    <script>
function btc_round(num) {
    return Math.round(num * Math.pow(10, 8)) / Math.pow(10, 8);
}
/**
 * Thermometer Progress meter.
 * This function will update the progress element in the "thermometer"
 * to the updated percentage.
 * If no parameters are passed in it will read them from the DOM
 *
 * @param {Number} goalAmount The Goal amount, this represents the 100% mark
 * @param {Number} progressAmount The progress amount is the current amount
 * @param {Boolean} animate Whether to animate the height or not
 *
 */
function thermometer(id, goalAmount, progressAmount, animate) {
    "use strict";

    var $thermo = $("#" + id),
        $progress = $(".progress", $thermo),
        $goal = $(".goal", $thermo),
        percentageAmount,
        isHorizontal = $thermo.hasClass("horizontal"),
        newCSS = {};
    $("#goalleft").hide();
    goalAmount = goalAmount || parseFloat($goal.text()),
    progressAmount = progressAmount || parseFloat($progress.text()),
    percentageAmount = Math.min(Math.round(progressAmount / goalAmount * 1000) / 10, 100); //make sure we have 1 decimal point

    //let's format the numbers and put them back in the DOM
    $goal.find(".amount").text(btc_round(goalAmount) + " BTC");
    $progress.find(".amount").text(btc_round(progressAmount) + " BTC");


    //let's set the progress indicator
    $progress.find(".amount").hide();

    newCSS[isHorizontal ? "width" : "height"] = percentageAmount + "%";

    if (animate !== false) {
        $progress.animate(newCSS, 1200, function () {
            $(this).find(".amount").fadeIn(500);
            $("#goalleft").show();
        });
    } else {
        $progress.css(newCSS);
        $progress.find(".amount").fadeIn(500);

    }
}

$(document).ready(function () {
    var current = COINWIDGETCOM_DATA[0].amount; //loaded dynamically
    var goal = 55.0;

    $("#goalleft").html("We only have " + btc_round(goal - current) + " BTC left to go!");
    thermometer("thermo2", goal, current);
});		
    </script>
    <?php echo $header_meta; ?>
  </head>
  <body>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
			
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          
          <a class="brand" href="<?php echo site_url(); ?>"><?php echo $site_title; ?></a>
