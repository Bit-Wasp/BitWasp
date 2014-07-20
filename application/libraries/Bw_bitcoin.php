<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Bitcoin Library
 *
 * This library is a socket for the JSON RPC interface.
 * Configuration is loaded from ./application/config/bitcoin.php
 * The class contains functions for bitcoind and functions for
 * bitcoind to callback in order to track information about new transactions.
 * Also contains a function to update exchange rates from the selected
 * provider.
 *
 * @package        BitWasp
 * @subpackage    Libraries
 * @category    Bitcoin
 * @author        BitWasp
 */
class Bw_bitcoin
{

    /**
     * CI
     */
    protected $CI;

    /**
     * Config
     *
     * This variable contains the bitcoin credentials for the JSON rpc
     * interface.
     */
    public $config;

    /**
     * Testnet
     *
     * Flag to tell the site if we are currently working in the testnet or
     * the main bitcoin chain.
     */
    public $testnet;

    /**
     * Constructor
     *
     * Load the bitcoin configuration using CodeIgniters config library.
     * Load the jsonRPCclient library with the config, and the bitcoin
     * model
     */
    public function __construct()
    {
        $this->CI = & get_instance();

        $this->CI->config->load('bitcoin', TRUE);
        $this->config = $this->CI->config->item('bitcoin');

        $this->CI->load->library('jsonrpcclient', $this->config);
        $this->CI->load->model('bitcoin_model');
    }

    /**
     * Get Exchange Rates
     *
     * Load exchange rates from the defined BPI. Called by bw_bitcoin/ratenotify().
     *
     * @return        array/FALSE
     */
    public function get_exchange_rates()
    {
        $this->CI->load->library('bw_curl');

        $source = $this->CI->bw_config->bitcoin_rate_config();
        $source_name = $this->CI->bw_config->price_index;

        $json_result = $this->CI->bw_curl->get_request($source['url']);

        if ($json_result == NULL)
            return FALSE;

        $array = json_decode($json_result);
        if ($array !== FALSE && $array !== NULL) {
            $array->price_index = $source_name;
            return $array;
        } else {
            return FALSE;
        }
    }

    /**
     * Get Block
     *
     * Function to query bitcoind, to get information about a block ($block_hash)
     * Returns an array containing the account name if successful, or
     * an array describing the error on failure.
     *
     * @param        string $block_hash
     * @return        array
     */
    public function getblock($block_hash)
    {
        return $this->CI->jsonrpcclient->getblock($block_hash);
    }

    /**
     * Get Block Hash
     *
     * Function to query bitcoind, to get the block hash for a particular
     * height.
     * Returns a string containing the block hash if successful, or an
     * array describing the error on failure.
     *
     * @param        string $block_no
     * @return        string / array
     */
    public function getblockhash($block_no)
    {
        return $this->CI->jsonrpcclient->getblockhash($block_no);
    }

    /**
     * Get Info
     *
     * Function to query bitcoind for general information, like version,
     * block height, balance, difficulty,
     *
     * @param        string
     * @return        string / array
     */
    public function getinfo()
    {
        return $this->CI->jsonrpcclient->getinfo();
    }

    /**
     * Get Raw Transaction
     *
     * Ask bitcoind to return the raw transaction, identified by $transaction_id
     *
     * @param    string $transaction_id
     * @return    string
     */
    public function getrawtransaction($transaction_id)
    {
        return $this->CI->jsonrpcclient->getrawtransaction($transaction_id);
    }

    /**
     * Create Raw Transaction
     *
     * This function works with the output of the bw_transaction->generate()
     * function. This is an array with the indexes 'inputs', and 'outputs'.
     * These are sent to bitcoind to be built into a transaction. Returns
     * the raw transaction hex, or an error array.
     *
     * @param    array $transaction
     * @return    string
     */
    public function createrawtransaction($transaction)
    {
        return $this->CI->jsonrpcclient->createrawtransaction($transaction['inputs'], $transaction['outputs']);
    }

    /**
     * Decode Raw Transaction
     *
     * Decodes raw $transaction_hex into an array. $transaction_hex is
     * the output of getrawtransaction, or createrawtransaction.
     *
     * @param    string $transaction_hex
     * @return    array
     */
    public function decoderawtransaction($transaction_hex)
    {
        return $this->CI->jsonrpcclient->decoderawtransaction($transaction_hex);
    }

    /**
     * Sign Raw Transaction
     *
     * This function when provided with only $transaction_hex will attempt
     * to sign the transaction using any keys found in the wallet.
     * If the transaction is unsigned, then the inputs are required. $inputs
     * is an array.
     * If the wallet does not have the private key, then it will need to
     * be supplied as a third parameter. A private key, or several, are
     * given as an array.
     * Returns an array containing:
     *  hex: a string - the transaction hex
     *  complete: boolean - indicating whether the transaction is fully signed.
     *
     * @param    string $transaction_hex
     * @param    array $inputs
     * @param    array $privkeys
     * @return    array
     */
    public function signrawtransaction($transaction_hex, $inputs = NULL, $privkeys = NULL)
    {
        if ($inputs == NULL)
            return $this->CI->jsonrpcclient->signrawtransaction($transaction_hex);
        if ($privkeys == NULL)
            return $this->CI->jsonrpcclient->signrawtransaction($transaction_hex, $inputs);

        return $this->CI->jsonrpcclient->signrawtransaction($transaction_hex, $inputs, $privkeys);
    }

    /**
     * Send Raw Transaction
     *
     * This function allows you to submit a raw transaction to be broadcast,
     * or just to import it to the bitcoin node. Returns a string containing
     * the transaction_id, or else an error array.
     *
     * @param    string $transaction_hex
     * @return    string/array
     */
    public function sendrawtransaction($transaction_hex)
    {
        return $this->CI->jsonrpcclient->sendrawtransaction($transaction_hex);
    }

    /**
     * Add Multisig Address
     *
     * This function is used to import a multisignature address into
     * the bitcoin wallet. This is required by users when signing an
     * unsigned/partially-signed transaction.
     * $m determines how many keys out of the total are needed to redeem funds.
     * $public_keys is an array containing the public keys. Order is important.
     * $account - defaults to the main account.
     *
     * @param    int $m
     * @param    array $public_keys
     * @param    (opt)array    $account
     * @return    string
     */
    /**
     * Add Multisig Address
     *
     * This function is used to import a multisignature address into
     * the bitcoin wallet. This is required by users when signing an
     * unsigned/partially-signed transaction.
     * $m determines how many keys out of the total are needed to redeem funds.
     * $public_keys is an array containing the public keys. Order is important.
     * $account - defaults to the main account.
     * @param $m
     * @param $public_keys
     * @param string $account
     * @return mixed
     */
    public function addmultisigaddress($m, $public_keys, $account = "")
    {
        return $this->CI->jsonrpcclient->addmultisigaddress($m, $public_keys, $account);
    }

    /**
     * Create Multisig
     *
     * This function creates a multisignature address for $m out of the
     * total number of $public keys. Does not import to the wallet, but
     * generates the redeemScript, which contains all users need to verify
     * the address, and that it is composed of the correct keys.
     * Returns an array containing the redeemScript and address.
     *
     * @param    int $m
     * @param    array $public_keys
     * @return    array
     */
    public function createmultisig($m, $public_keys)
    {
        return $this->CI->jsonrpcclient->createmultisig($m, $public_keys);
    }

    /**
     * Import Private Key
     *
     * Function to ask bitcoind to import the wallet import format private
     * key $wif, in WIF format. $account defaults to the main account,
     * and $rescan left at the default will trigger a reindex of the
     * blockchain to search for transactions. This should be set to FALSE
     * if the key is only to be used for signing.
     *
     * @param $wif
     * @param string $account
     * @param bool $rescan
     * @return mixed
     */
    public function importprivkey($wif, $account = '', $rescan = TRUE)
    {
        return $this->CI->jsonrpcclient->importprivkey("$wif", "$account", $rescan);
    }

    /**
     * Get Inputs PkScripts
     *
     * This function generates JSON inputs for any inputs that are given
     * to it. $inputs is an array, where each input is a child array
     * containing [txid, vout].
     *
     * Returns a JSON string.
     *
     * @param    array $inputs
     * @return    array
     */
    public function get_inputs_pkscripts($inputs)
    {
        $results = array();
        foreach ($inputs as $input) {
            $result = array('txid' => $input['txid'],
                'vout' => (int)$input['vout']);
            $outpoint = $this->decoderawtransaction($this->getrawtransaction($input['txid']));
            $result['scriptPubKey'] = $outpoint['vout'][$input['vout']]['scriptPubKey']['hex'];
            $result['amount'] = $outpoint['vout'][$input['vout']]['value'];

            $results[] = $result;
        }
        return $results;
    }

    /**
     * Rate Notify
     *
     * Function to query the selected bitcoin price index provider
     * for the latest exchange rates between USD/GBP/EUR.
     *
     * @return        boolean
     */
    public function ratenotify()
    {
        $this->CI->load->model('currencies_model');
        // Abort if price indexing is disabled.
        if ($this->CI->bw_config->price_index == 'Disabled')
            return TRUE;

        // Function to get the exchange rates via an API.
        $rates = $this->get_exchange_rates();

        if ($rates == FALSE) {
            $this->CI->logs_model->add('Price Index', 'Unable to fetch exchange rates', 'An attempt to update the Bitcoin Exchange rates failed. Please review your ./application/config/bitcoin_index.php file for any errors, or that the proxy is correctly configured', 'Error');
            return TRUE;
        }

        // Parse results depending on where they're from.
        if ($this->CI->bw_config->price_index == 'CoinDesk') {
            $update = array('time' => strtotime($rates->time->updated),
                'usd' => str_replace(",", "", $rates->bpi->USD->rate),
                'gbp' => str_replace(",", "", $rates->bpi->GBP->rate),
                'eur' => str_replace(",", "", $rates->bpi->EUR->rate),
                'price_index' => $rates->price_index
            );
        } else if ($this->CI->bw_config->price_index == 'BitcoinAverage') {
            $update = array('time' => strtotime($rates->timestamp),
                'usd' => ($rates->USD->averages->last !== '0.0000') ? str_replace(",", "", $rates->USD->averages->last) : $this->CI->currencies_model->get_exchange_rate('usd'),
                'gbp' => ($rates->GBP->averages->last !== '0.0000') ? str_replace(",", "", $rates->GBP->averages->last) : $this->CI->currencies_model->get_exchange_rate('gbp'),
                'eur' => ($rates->EUR->averages->last !== '0.0000') ? str_replace(",", "", $rates->EUR->averages->last) : $this->CI->currencies_model->get_exchange_rate('eur'),
                'price_index' => $rates->price_index
            );
        }

        return (isset($update) && $this->CI->currencies_model->update_exchange_rates($update) == TRUE) ? TRUE : FALSE;
    }

    /**
     * Check Alert
     *
     * Query bitcoin daemon for an alert. Returns an array detailing the
     * message and that it came from the bitcoin daemon. Otherwise it
     * returns FALSE.
     *
     * @return    array/FALSE
     */
    public function check_alert()
    {

        // Return false if the bitcoin daemon is offline.
        $info = $this->getinfo();
        if (!is_array($info))
            return FALSE;

        // Return the string if there's an alert, otherwise false.
        return (is_string($info['errors']) && strlen($info['errors']) > 0) ? array('message' => $info['errors'], 'source' => 'Bitcoin') : FALSE;
    }

    public function associate_sigs_with_keys($raw_tx, $json_string, $address_version = '00')
    {
        $raw_tx = trim($raw_tx);
        $json_string = str_replace("'", '', $json_string);
        $decode = \BitWasp\BitcoinLib\RawTransaction::decode($raw_tx, $address_version);
        if ($decode == FALSE)
            return FALSE;

        $json_arr = (array)json_decode($json_string);

        $message_hash = \BitWasp\BitcoinLib\RawTransaction::_create_txin_signature_hash($raw_tx, $json_string);

        $results = array();
        foreach ($decode['vin'] as $i => $vin) {
            // Decode previous scriptPubKey to learn tramsaction type.
            $type_info = \BitWasp\BitcoinLib\RawTransaction::_get_transaction_type(\BitWasp\BitcoinLib\RawTransaction::_decode_scriptPubKey($json_arr[$i]->scriptPubKey));

            if ($type_info['type'] == 'scripthash') {
                // Pay-to-script-hash. Check OP_FALSE <sig> ... <redeemScript>
                // Store the redeemScript, then remove OP_FALSE + the redeemScript from the array.
                $scripts = explode(" ", $vin['scriptSig']['asm']);
                $redeemScript = \BitWasp\BitcoinLib\RawTransaction::decode_redeem_script(end($scripts));
                unset($scripts[(count($scripts) - 1)]); // Unset redeemScript
                unset($scripts[0]); // Unset '0';

                // Extract signatures, remove the "0" byte, and redeemScript.
                // Loop through the remaining values - the signatures
                foreach ($scripts as $signature) {
                    // Test each signature with the public keys in the redeemScript.
                    foreach ($redeemScript['keys'] as $public_key) {
                        if (\BitWasp\BitcoinLib\RawTransaction::_check_sig($signature, $message_hash[$i], $public_key) == TRUE)
                            $results[$i][$public_key] = $signature;

                    }
                }
            }
        }
        return $results;
    }

    public function handle_order_tx_submission($order, $incoming_tx, $user_bip32_key)
    {
        $this->CI->load->model('transaction_cache_model');
        $currently_unsigned = strlen($order['partially_signed_transaction']) == 0;

        if ($currently_unsigned) {
            $start_tx = trim($order['unsigned_transaction']);
        } else {
            $start_tx = trim($order['partially_signed_transaction']);
        }

        $json = str_replace("'", '', $order['json_inputs']);
        $decode_current_tx = \BitWasp\BitcoinLib\RawTransaction::decode($start_tx);
        $decode_incoming_tx = \BitWasp\BitcoinLib\RawTransaction::decode($incoming_tx);

        // Does incoming tx match expected spend?
        $check = $this->CI->transaction_cache_model->check_if_expected_spend($decode_incoming_tx['vout'], $order['id']);
        if ($check !== $order['address'])
            return 'Invalid transaction.';

        // General check that signatures match tx
        $validate = \BitWasp\BitcoinLib\RawTransaction::validate_signed_transaction($incoming_tx, $json);
        if ($validate == FALSE) {
            return 'Invalid signature.';
        }

        $decode_redeem_script = \BitWasp\BitcoinLib\RawTransaction::decode_redeem_script($order['redeemScript']);

        if (!$currently_unsigned AND $user_bip32_key['provider'] == 'JS') {
            // Need to build the sig from this tx into the last.
            $copy = $decode_incoming_tx;

            foreach ($copy['vin'] as $i => &$input) {
                $script = explode(" ", $input['scriptSig']['asm']);
                $sig1 = \BitWasp\BitcoinLib\RawTransaction::_encode_vint((strlen($script[1]) / 2)) . $script[1];

                $old_script = explode(" ", $decode_current_tx['vin'][$i]['scriptSig']['asm']);
                $sig2 = \BitWasp\BitcoinLib\RawTransaction::_encode_vint((strlen($old_script[1]) / 2)) . $old_script[1];

                $redeem_script = '4c' . \BitWasp\BitcoinLib\RawTransaction::_encode_vint((strlen($order['redeemScript']) / 2)) . $order['redeemScript'];
                $input['scriptSig']['hex'] = '00' . $sig1 . $sig2 . $redeem_script;
            }
            $incoming_tx = \BitWasp\BitcoinLib\RawTransaction::encode($copy);
            // Now need to reorder sigs!
            $assoc = $this->associate_sigs_with_keys($incoming_tx, $json, $this->CI->bw_config->currencies[0]['crypto_magic_byte']);
            foreach ($copy['vin'] as $i => &$input) {
                $input['scriptSig']['hex'] = \BitWasp\BitcoinLib\RawTransaction::_apply_sig_scripthash_multisig($assoc[$i], array('public_keys' => $decode_redeem_script['keys'], 'script' => $order['redeemScript']));
            }
            $incoming_tx = \BitWasp\BitcoinLib\RawTransaction::encode($copy);
            $decode_incoming_tx = \BitWasp\BitcoinLib\RawTransaction::decode($incoming_tx);
        }

        // Compare signatures!
        $old_sig_map = $this->associate_sigs_with_keys($start_tx, $json, $this->CI->bw_config->currencies[0]['crypto_magic_byte']);

        // Now check current signatures against users key. submittee must have signed.
        $key_sig_map = $this->associate_sigs_with_keys($incoming_tx, $json, $this->CI->bw_config->currencies[0]['crypto_magic_byte']);

        foreach ($key_sig_map as $i => $input_sig_map) {
            // If the number of sigs hasn't increased, or no sig from the current user exists..
            if (count($old_sig_map) > 0 && count($input_sig_map) <= count($old_sig_map[$i])
                OR !isset($input_sig_map[$user_bip32_key['public_key']])
            )
                return 'Incorrect signature!';
        }

        // Broadcast tx if fully signed!
        if (!$currently_unsigned) {
            $this->CI->transaction_cache_model->to_broadcast($incoming_tx);
            $this->sendrawtransaction($incoming_tx);
        }

        return TRUE;
    }

    public function js_html($redeem_script, $raw_transaction)
    {
        if (strlen($redeem_script) == '0' || strlen($raw_transaction) == '0')
            return '';

        $this->CI->load->model('transaction_cache_model');
        $decode_rs = \BitWasp\BitcoinLib\RawTransaction::decode_redeem_script($redeem_script);
        $pubkey_list = '';
        foreach ($decode_rs['keys'] as $i => $key)
            $pubkey_list .= '    "' . $key . '"' . (($i < 2) ? ',' : '') . "\n";

        $p2sh_info = \BitWasp\BitcoinLib\RawTransaction::create_multisig(2, $decode_rs['keys']);
        $decode_tx = \BitWasp\BitcoinLib\RawTransaction::decode($raw_transaction);
        $utxos = "";
        foreach ($decode_tx['vin'] as $vin => $input) {
            $tx_info = $this->CI->transaction_cache_model->get_payment($input['txid']);
            $utxos .= "
{
    address: '{$p2sh_info['address']}',
    txid: '{$tx_info['tx_id']}',
    vout: {$tx_info['vout']},
    scriptPubKey: '{$tx_info['pkScript']}',
    confirmations: 10,
    amount: " . $tx_info['value'] . "
}" . (($vin < count($decode_tx['vin']) - 1) ? ',' : '') . "\n";
//".($current_block-$tx_info['block_height']).",
        }

        $outs = '';
        foreach ($decode_tx['vout'] as $vout => $output) {
            $outs .= '{
    address: "' . $output['scriptPubKey']['addresses'][0] . '",
    amount: ' . $output['value'] . "
}" . ($vout < (count($decode_tx['vout']) - 1) ? ',' : '');
        }
        $json = "
var pubkeys = [
{$pubkey_list}
];

var opts = {
    nreq: 2,
    pubkeys: pubkeys
};

var serialized_pubkeys = [];
for (var i=0; i<3; i++) {
    serialized_pubkeys.push(new bitcore.buffertools.Buffer(opts.pubkeys[i],'hex'));
}

var script = bitcore.Script.createMultisig(opts.nreq, serialized_pubkeys, {noSorting: true});
var hash   = bitcore.util.sha256ripe160(script.getBuffer());


var p2shScript = script.serialize().toString('hex');
var p2shAddress = new bitcore.Address.fromScript(p2shScript).toString();
console.log('got address: '+p2shAddress);

var utxos = [
{$utxos}
];

var outs = [
{$outs}
];

var hashMap = {};
hashMap[p2shAddress] = p2shScript;
        ";

        return $json;
    }
}

;
