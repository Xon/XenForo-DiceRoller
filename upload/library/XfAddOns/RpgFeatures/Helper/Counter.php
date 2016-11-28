<?php

/**
 * Functions used by the counter
 */
class XfAddOns_RpgFeatures_Helper_Counter
{

	/**
	 * Returns an array with the time difference (hours, minutes, seconds, etc)
	 * @param int $millis
	 */
	public static function getTimeParams($millis)
	{
		if ($millis <= 0) {
			$seconds = 0;
			$minutes = 0;
			$hours = 0;
			$days = 0;
			$weeks = 0;
		}
		else {
			$seconds = $millis % 60;
			$minutes = floor($millis % 3600 / 60);
			$hours = floor($millis % 86400 / 3600);
			$days = floor($millis / 86400) % 7;
			$weeks = floor(floor($millis / 86400) / 7);
		}

		return array(
			'weeks' => array ( floor($weeks / 10), $weeks % 10, $weeks),
			'days' => array ( floor($days / 10), $days % 10, $days ),
			'hours' => array ( floor($hours / 10), $hours % 10, $hours ),
			'minutes' => array ( floor($minutes / 10), $minutes % 10, $minutes ),
			'seconds' => array ( floor($seconds / 10), $seconds % 10, $seconds )
			);
	}

}