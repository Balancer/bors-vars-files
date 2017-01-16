<?php

class bors_var
{
	static function get($name, $default = NULL)
	{
		if(!preg_match('/^[\w\-]+$/', $name))
			$name = base64_encode($name);

		$file = COMPOSER_ROOT."/data/bors/vars/$name.json";

		if(!file_exists($file))
			return $default;

		$data = json_decode(file_get_contents($file), true);

		if($data['expire_time'] > 0 && $data['expire_time'] <= time())
		{
			unlink($file);
			return $default;
		}

		return $data['value'];
	}

	static function set($name, $value, $time_to_live = NULL)
	{
		if(!preg_match('/^[\w\-]+$/', $name))
			$name = base64_encode($name);

		$file = COMPOSER_ROOT."/data/bors/vars/$name.json";

		mkpath(dirname($file), 0777);

		$expire = $time_to_live > 0 ? time() + $time_to_live : $time_to_live;

		\B2\Files::put_lock($file, json_encode([
			'name' => $name,
			'value' => $value,
			'expire_time' => $expire,
		]));

		if($time_to_live > 0)
			touch($file, time(), $expire);

		return $value;
	}
}
