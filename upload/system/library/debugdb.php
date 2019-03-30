<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @class by	Cuong Nguyen
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use DebugBar\DebugBarException;

class Debugdb extends DataCollector implements Renderable, AssetProvider
{	
	private $queries;
    public function __construct($queries)
    {
        $this->queries = $queries;
    }

    public function collect()
    {
        $totalExecTime = 0;
		$queries = array_map(function($query) use(&$totalExecTime) {
			$totalExecTime += $query['duration'];
			return array(
				'sql'		=> $query['query'], 
				'duration'	=> $query['duration'], 
				'duration_str' => $this->formatDuration($query['duration'])
			);	
		}, $this->queries );
		
        return array(
            'nb_statements' => count($queries),
            'accumulated_duration' => $totalExecTime,
            'accumulated_duration_str' => $this->formatDuration($totalExecTime),
            'statements' => $queries
        );
    }

    public function getName()
    {
        return 'ddb';
    }

    public function getWidgets()
    {
        return array(
            "database" => array(
                "icon" => "arrow-right",
                "widget" => "PhpDebugBar.Widgets.SQLQueriesWidget",
                "map" => "ddb",
                "default" => "[]"
            ),
            "database:badge" => array(
                "map" => "ddb.nb_statements",
                "default" => 0
            )
        );
    }

    public function getStyle() {
        return 'catalog/view/javascript/debugbar/widgets/sqlqueries/widget.css';
    }

    public function getScript() {
        return 'catalog/view/javascript/debugbar/widgets/sqlqueries/widget.js';
    }

    public function getAssets() {
    }
}