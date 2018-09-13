<?php
/**
* @version		2.0
* @package		DJ Classifieds
* @subpackage	DJ Classifieds Stats Module
* @copyright	Copyright (C) 2010 DJ-Extensions.com LTD, All rights reserved.
* @license		http://www.gnu.org/licenses GNU/GPL
* @autor url    http://design-joomla.eu
* @autor email  contact@design-joomla.eu
* @Developer    Lukasz Ciastek - lukasz.ciastek@design-joomla.eu
* @Modyfied by  Pawe� Stolarski
* 
* 
* DJ Classifieds is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* DJ Classifieds is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with DJ Classifieds. If not, see <http://www.gnu.org/licenses/>.
* 
*/
defined ('_JEXEC') or die('Restricted access');

  function DEBUG($item, $die=true){
    echo '<pre>';
    print_r($item);
    echo '</pre>';
    if($die) die();
  }

  function dateExploder($m,$d,$y){
    return explode('-',date("Y-m-d",mktime(0,0,0,$m,$d,$y)));
  }

  class modDjClassifiedsStats {
    	
    public static function getStats(
      $table=false   ,$select=false      ,$count_cell=false
    , $date_type=['m'] ,$date_field='date' ,$date_range_state='' ,$date_range=0
    , $how_many=5    ,$desc=''         ,$join=false
    ){
        if(!$table || !$select) return '';
        $db   = JFactory::getDBO();
        $group = explode(',' ,$select)[0];
        $date = [[0,0],0,0,0];

        if($date_type[0]=='y' || $date_type[0]=='Y'){ $date[3] = ($date_range | 5)  ;$date[0][1]='DAY'  ;}
        if($date_type[0]=='m' || $date_type[0]=='M'){ $date[2] = ($date_range | 12) ;$date[0][1]='MONTH';}
        if($date_type[0]=='d' || $date_type[0]=='D'){ $date[1] = ($date_range | 7)  ;$date[0][1]='YEAR' ;}
        $date[0][0] = date("Y-m-d",mktime(0,0,0,date("m")-$date[2],date("d")-$date[1],date("Y")-$date[3]));

        if(gettype($join)==='array') $join = ' JOIN '. $join[0] .' AS b ON a.'. $join[1] .'=b.'. $join[2];
        if($date_range_state===true){
          $date_from = $date_type[1] ? $date_type[1] : $date[0][0];
          $date_to   = $date_type[2] ? '"'.$date_type[2].'"' : 'CURDATE()';

          $date_range_state = ' WHERE DATE('.$date_field.') BETWEEN "'.$date_from.'" AND '.$date_to;
        }
        if($desc===true) $desc =  ' DESC';

        $query = "SELECT $select, $count_cell AS total"
          . " FROM $table AS a"
          . $join
          . $date_range_state
          . " GROUP BY $group ORDER BY total"
          . $desc
          ;

        $db->setQuery($query ,0 ,$how_many);
        return $db->loadObjectList();
    }
/*
	public static function getGraphs($table=false ,$date=['y'] ,$select='COUNT(*)' ,$date_field='date'){
        if(!$table) return '';
        $db        = JFactory::getDBO();
        $date_from = $date[1] ? explode('-',$date[1]) : null;
        $date_to   = $date[2] ? explode('-',$date[2]) : explode('-',date('Y-m-d'));
        $unit      = [];
        $db_res    = [];
        $db_dates  = [];
        $results   = [];
		
		JFactory::getApplication()->enqueueMessage("<pre>".print_r($date, true)."</pre>");

        switch($date[0]){
          case 'd':
          case 'D':
            $date[0] = 'd';
            $unit = ['DAY' ,2];
            if(!$how_many || $how_many > 15){
              if($date_from){
                $once = date_create(join('-', $date_from));
                $now  = date_create(join('-', $date_to));

                if($once->diff($now)->y > 15){
                  $date_from = dateExploder( date("m"),date("d")-$how_many,date("Y") );
                  $how_many = 15;
                }else{
                  $how_many = $once->diff($now)->d+1;
                }
              }else{
                $how_many = 7;
              }
            }
            if(!$date_from){
              $date_from = dateExploder( date("m"),date("d")-$how_many,date("Y") );
            }
            break;

          case 'm':
          case 'M':
            $date[0] = 'm';
            $unit = ['MONTH' ,1];
            if(!$how_many || $how_many > 15){
              if($date_from){
                $once = date_create(join('-', $date_from));
                $now  = date_create(join('-', $date_to));

                if($once->diff($now)->y > 15){
                  $date_from = dateExploder( date("m")-15,1,date("Y") );
                  $how_many = 15;
                }else{
                  $how_many = $once->diff($now)->m+1;
                }
              }else{
                $how_many = 12;
              }
            }
            if(!$date_from){
              $date_from = dateExploder( date("m")-$how_many,1,date("Y") );
            }
            break;

          case 'y':
          default:
            $date[0] = 'Y';
            $unit = ['YEAR' ,0];
            if(!$how_many || $how_many > 15){
              if($date_from){
                $once = date_create(join('-', $date_from));
                $now  = date_create(join('-', $date_to));

                if($once->diff($now)->y > 15){
                  $date_from = dateExploder( 1,1,date("Y")+1-15 );
                  $how_many = 15;
                }else{
                  $how_many = $once->diff($now)->y+1;
                }
              }else{
                $how_many = 5;
              }
            }
            if(!$date_from){
              $date_from = dateExploder( 1,1,date("Y")+1-$how_many );
            }
            break;
        }

        $date_range_state = ' WHERE DATE('.$date_field.') BETWEEN "'.join('-',$date_from).'" AND "'.join('-',$date_to).'"';
		
		JFactory::getApplication()->enqueueMessage("<pre>".print_r($date_range_state, true)."</pre>");
		
        $query = ''
          . 'SELECT '. $select .' AS total ,'. $unit[0] .'('. $date_field .') AS date'
          . ' FROM '. $table
          . $date_range_state
          . ' GROUP BY '. $unit[0] .'('. $date_field .')'
          ;

        $db->setQuery($query);
        $db_res = $db->loadObjectList();
		
		JFactory::getApplication()->enqueueMessage("<pre>".print_r($db_res, true)."</pre>");

        foreach($db_res as $v){
          $key = ( strlen($v->date)==1 ? '0':'' ) . $v->date;
          $db_dates[$key] = $v->total;
        }
        for($i=1; $i<=$how_many; $i++){
		  $mktime = mktime(0,0,0,$date_from[1],$date_from[2],$date_from[0]);
          $key = date(strtoupper($date[0]) ,$mktime);
          $k   = date($date[0],$mktime);
          $results[$key] = isset($db_dates[$k]) ? $db_dates[$k] : 0;
          ++$date_from[$unit[1]];
        }
		
		JFactory::getApplication()->enqueueMessage("<pre>".print_r($results, true)."</pre>");
		
        return $results;
    }
*/
	public static function debug($msg) {
		
		JFactory::getApplication()->enqueueMessage("<pre>".print_r($msg, true)."</pre>");
	}

    public static function getGraphs($table=false , $date=['m'] ,$select='COUNT(*)' ,$date_field='date'){
        
        if(!$table) return null;
        
        $db        = JFactory::getDBO();
        $date_from = $date[1] ? $date[1] : null;
        $date_to   = $date[2] ? $date[2] : JFactory::getDate()->format('Y-m-d');
        
		switch($date[0]) {
			case 'd':
				if(!$date_from) $date_from = JFactory::getDate($date_to.' -6 days')->format('Y-m-d');
				$group_by = ' DATE('. $date_field .') ';
				break;
			case 'm':
				$date_to = JFactory::getDate($date_to)->modify('last day of this month')->format('Y-m-d');
				if(!$date_from) $date_from = JFactory::getDate($date_to.' -11 months')->format('Y-m').'-01';
				else $date_from = JFactory::getDate($date_from)->format('Y-m').'-01';
				$group_by = ' DATE_FORMAT('. $date_field .', \'%Y-%m\') ';
				break;
			case 'y':
				$date_to = JFactory::getDate($date_to)->modify('last day of this year')->format('Y-m-d');
				if(!$date_from) $date_from = JFactory::getDate($date_to.' -4 years')->format('Y').'-01-01';
				else $date_from = JFactory::getDate($date_from)->format('Y').'-01-01';
				$group_by = ' YEAR('. $date_field .') ';
				break;
			default:
				return null;
		}
        
        $query = ''
          . ' SELECT '. $select .' AS total ,'. $group_by .' AS date'
          . ' FROM '. $table
          . ' WHERE DATE('.$date_field.') BETWEEN '.$db->quote($date_from).' AND '.$db->quote($date_to)
          . ' GROUP BY '.$group_by;

        $db->setQuery($query);
        $stats = $db->loadObjectList('date');
		
		$data = array();
		
		switch($date[0]) {
			case 'd':
				
				$from = $date_from;
				$to = JFactory::getDate($date_to.' +1 day')->format('Y-m-d');
				$year = (JFactory::getDate($date_to)->toUnix() - JFactory::getDate($date_from)->toUnix() > 365*24*60*60 ? ' \'y':'');
				
				while($from != $to) {
					
					$data[JFactory::getDate($from)->format('D, j M'.$year)] = isset($stats[$from]) ? $stats[$from]->total : 0;
					$from = JFactory::getDate($from.' +1 day')->format('Y-m-d');
				}
				
				break;
			case 'm':
				
				$from = JFactory::getDate($date_from)->format('Y-m');
				$to = JFactory::getDate($date_to.' +1 month')->format('Y-m');
				
				while($from != $to) {
					
					$data[JFactory::getDate($from.'-01')->format('M \'y')] = isset($stats[$from]) ? $stats[$from]->total : 0;
					$from = JFactory::getDate($from.'-01 +1 month')->format('Y-m');
				}
				
				break;
				
			case 'y':
				
				$from = JFactory::getDate($date_from)->format('Y');
				$to = JFactory::getDate($date_to.' +1 year')->format('Y');
				
				while($from != $to) {
					
					$data[$from] = isset($stats[$from]) ? $stats[$from]->total : 0;
					$from++;
				}
				
				break;
		}
		
		return $data;
    }

    public static function getAuctions(){
        $db= JFactory::getDBO();	
        $date_now = date("Y-m-d H:i:s");
        $query = "SELECT COUNT(id) FROM #__djcf_items i "
               . "WHERE i.date_exp > '".$date_now."' AND i.auction=1 AND i.published=1 AND i.blocked=0";
        $db->setQuery($query);
        $total=$db->loadResult();
  
      return $total;
    }
  
    public static function getCategories(){
        $db= JFactory::getDBO();
        $date_now = date("Y-m-d H:i:s");
        $query = "SELECT COUNT(id) FROM #__djcf_categories "
        		."WHERE published=1";
        $db->setQuery($query);
        $total=$db->loadResult();
  
        return $total;
    }
  
    public static function getAdverts($pub=1,$date_from=''){
        $db= JFactory::getDBO();
  
        $date_now = date("Y-m-d H:i:s");
        $pub_w = '';
        if($pub){
        	$pub_w = " AND i.published=1 AND i.blocked=0 AND i.date_exp > '".$date_now."' ";
        }
  
        $date_from_w = '';
        if($date_from){
        	$date_from_w = " AND i.date_start>='".$date_from."' ";
        }
  
        $query = "SELECT COUNT(id) FROM #__djcf_items i WHERE 1 ".$pub_w.$date_from_w;		
        $db->setQuery($query);
        $total=$db->loadResult();
        
        return $total;
    }
  }