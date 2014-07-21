      </div> <!-- /row-fluid -->
      <hr />
      <footer>
        <p class="pull-right">
            <i>
{if $footer.price_index != 'Disabled' && isset($footer.exchange_rates)}
	{foreach from=$footer.exchange_rates key=code item=rate}
		{if in_array($code, ['time','time_f']) != TRUE}
		{$rate|escape:"html":"UTF-8"}{strtoupper($code)} / 
		{/if}
	{/foreach}
    Obtained {$footer.exchange_rates.time_f} from {$footer.price_index}.
{/if}
                <a href="https://github.com/Bit-Wasp/BitWasp">Powered by BitWasp</a>
            </i>
        </p>
      </footer>
    </div>  <!-- /container -->

  </body>
</html>
