      </div> <!-- /row-fluid -->
      <hr />
      <footer>
        <p class="pull-right"><i>
<?php 
if($price_index !== 'Disabled' && isset($exchange_rates) && isset($exchange_rates['bpi'])) { 
	if($price_index !== 'Disabled') { 
		echo '1 BTC ';
		foreach($exchange_rates['bpi'] as $code => $rate) { echo '  /  '.$rate.strtoupper($code); } 
} ?> Data obtained <?php echo $exchange_rates['time_f']; ?> from <?php echo $price_index; ?>. 
<?php } ?></i>

Page rendered in <strong>{elapsed_time}</strong> seconds. <a href="https://github.com/Bit-Wasp/BitWasp">Powered by BitWasp</a></p>
      </footer>
    </div>  <!-- /container -->

  </body>
</html>
