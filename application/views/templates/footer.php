      </div> <!-- /row-fluid -->
      <hr />
      <footer>
        <p class="pull-right"><i>
<?php 
if($price_index !== 'Disabled' && isset($exchange_rates) ) {
		//echo '1 BTC ';
		foreach($exchange_rates as $code => $rate) {
            if(! in_array($code, array('time','time_f')))
                echo ' '.$rate.strtoupper($code)." / ";
        }
        ?> Obtained <?php echo $exchange_rates['time_f']; ?> from <?php echo $price_index; ?>.
<?php } ?></i>

Delivered in {elapsed_time} seconds. <a href="https://github.com/Bit-Wasp/BitWasp">Powered by BitWasp</a></p>
      </footer>
    </div>  <!-- /container -->

  </body>
</html>
