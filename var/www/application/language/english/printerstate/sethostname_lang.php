<?php
// general page
$lang['back']
	= 'Back';
$lang['home_button']
	= 'Home';

$lang['page_title']
	= 'Zim - Set hostname';
$lang['set_hint']
	= 'Customize your access link (hostname)';
$lang['set_button']
	= 'OK';
$lang['no_input']
	= 'No user input found';
$lang['set_error']
	= 'Set hostname error';
$lang['network_array_error']
	= 'Error while trying to get network information';
$lang['bad_char']
	= 'Please use only letters, numbers, and hyphens.';
$lang['p2p']
	= "Your zim will now start broadcasting a Wi-Fi network named %s. For better results, please restart zim using the power button.<br />Connect your device on the network mentioned above with the password you specified at the previous step, and you will be able to manage your printer with an internet browser by clicking <a href='http://10.0.0.1'>here</a> or typing http://10.0.0.1 in the address bar.";
$lang['finish_hint']
	= 'Your zim will restart to apply its new parameters.</br>
		</br>
		Reconnect to your usual network to access your zim using the link:</br>
		</br>
		<a href="http://%s.local/">http://%s.local/</a> or <a href="http://%s">http://%s</a>';
$lang['finish_hint_returnUrl']
	= 'Return your printer\'s information page using this link:</br>
		</br>
		<a href="http://%s.local/%s">http://%s.local/%s</a> or <a href="http://%s/%s">http://%s/%s</a>';
$lang['finish_hint_norestart']
	= 'Your printer will restart to apply its new parameters.</br>
		</br>
		Reconnect to your usual network to access your printer using the link:</br>
		</br>
		http://%s.local/ or http://%s';
$lang['length_error'] = "Limited to 9 characters";
$lang['info_text']
	= 'From your local network, you can reach your zim from "http://<span id="fqdn">xxx</span>.local" if you use an Apple, Android or Linux device, and from "http://<span id="fqdn2">xxx</span>" if you\'re using Windows.';