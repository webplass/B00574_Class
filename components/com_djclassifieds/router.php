<?php
/**
* @version		2.0
* @package		DJ Classifieds
* @subpackage	DJ Classifieds Component
* @copyright	Copyright (C) 2010 DJ-Extensions.com LTD, All rights reserved.
* @license		http://www.gnu.org/licenses GNU/GPL
* @autor url    http://design-joomla.eu
* @autor email  contact@design-joomla.eu
* @Developer    Lukasz Ciastek - lukasz.ciastek@design-joomla.eu
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
defined('_JEXEC') or die;
if(!defined("DS")){
	define('DS',DIRECTORY_SEPARATOR);
}
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_djclassifieds'.DS.'lib'.DS.'djseo.php');

function DJClassifiedsBuildRoute(&$query)
{
    $segments = array();
    $app        = JFactory::getApplication();
    $menu       = $app->getMenu('site');
	$par 		= JComponentHelper::getParams( 'com_djclassifieds' );

    if (empty($query['Itemid'])) {
        $menuItem = $menu->getActive();
    } else {
        $menuItem = $menu->getItem($query['Itemid']);
    }
    $option = (empty($menuItem->component)) ? null : $menuItem->component;
    
    $mView  = (empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
    $mLayout = (empty($menuItem->query['layout'])) ? null : $menuItem->query['layout'];
    $mCatid = (empty($menuItem->query['cid'])) ? null : (int)$menuItem->query['cid'];
    $mRegid = (empty($menuItem->query['rid'])) ? null : (int)$menuItem->query['rid'];
    $mId    = (empty($menuItem->query['id'])) ? null : (int)$menuItem->query['id'];
    
    $view = !empty($query['view']) ? $query['view'] : null;
	$layout = !empty($query['layout']) ? $query['layout'] : null;
    $cid = !empty($query['cid']) ? $query['cid'] : null;
    $rid = !empty($query['rid']) ? $query['rid'] : null;
    $id = !empty($query['id']) ? $query['id'] : null;
	$se = !empty($query['se']) ? $query['se'] : null;
	$uid = !empty($query['uid']) ? $query['uid'] : null;
	$bid = !empty($query['bid']) ? $query['bid'] : null;
	$order = !empty($query['order']) ? $query['order'] : null;
	$menuDefault = $menu->getDefault();
    
    // JoomSEF bug workaround
    if (isset($query['start']) && isset($query['limitstart'])) {
    	if ((int)$query['limitstart'] != (int)$query['start'] && (int)$query['start'] > 0) {
    		// let's make it clear - 'limitstart' has higher priority than 'start' parameter, 
    		// however ARTIO JoomSEF doesn't seem to respect that.
    		$query['start'] = $query['limitstart'];
    		unset($query['limitstart']);
    	}
    }
    // JoomSEF workaround - end

    if ($view && $option == 'com_djclassifieds') {      		                
    	if ($view == 'item') {
    		if ($view != $mView) {
				$segments[]=$par->get('seo_view_item','ad');
	        }    		
			unset($query['view']);	
        	if ($view == $mView && intval($id) > 0 && intval($id) == $mId) {
        		unset($query['id']);
        		unset($query['cid']);
        		unset($query['rid']);
        	} else if ($mView == 'items' && intval($id) > 0) {
        		$segment_cid = '';
        		if (intval($cid) != intval($mCatid)) {
					$segment_cid = DJClassifiedsSEO::getURLfromSlug($cid);
        		}
        		$segment_rid = '';
        		if (intval($rid) != intval($mRegid)) {
        			$segment_rid =DJClassifiedsSEO::getURLfromSlug($rid,'l');
        		}
        		
        		if($par->get('seo_item_url_structure',0)==1){ //region/category
        			if($segment_rid){
        				$segments[] = $segment_rid;
        			}
        			if($segment_cid){
        				$segments[] = $segment_cid;
        			}        			
        		}else if($par->get('seo_item_url_structure',0)==2){ //category/region
        			if($segment_cid){
        				$segments[] = $segment_cid;
        			}
        			if($segment_rid){
        				$segments[] = $segment_rid;
        			}
        			
        		}else { //only category
        			if($segment_cid){
        				$segments[] = $segment_cid;
        			}
        		}
        		
        		
        		$segments[] =DJClassifiedsSEO::getURLfromSlug($id);
        		unset($query['id']);
        		unset($query['cid']);
        		unset($query['rid']);
        	}
        }else if ($view == 'items') {
        	if ($view != $mView) {
				$segments[]=$par->get('seo_view_items','ads');
	        }
        	if ($cid === null && $rid === null) {
        		//$cid = '0:all'; 
        	}
        	$segment_cid = '';
	        if($cid){
	            if (intval($cid) != intval($mCatid)) {            	
					//$segments[] = $cid;
	            	//$segments[] =DJClassifiedsSEO::getURLfromSlug($cid);
	            	$segment_cid = DJClassifiedsSEO::getURLfromSlug($cid);
					
	            }
	            unset($query['cid']);
	        }
            
	        $segment_rid = '';
            if($rid){
            	if (intval($rid) != intval($mRegid)) {
            		$segment_rid = DJClassifiedsSEO::getURLfromSlug($rid,'l');
            	}
            	unset($query['rid']);
            }            
            
            if($par->get('seo_items_url_structure',0)){ //category/region
            	if($segment_cid){
            		$segments[] = $segment_cid;
            	}
            	if($segment_rid){
            		$segments[] = $segment_rid;
            	}            	 
            }else{ //region/category
            	if($segment_rid){
            		$segments[] = $segment_rid;
            	}
            	if($segment_cid){
            		$segments[] = $segment_cid;
            	}
            }
            
            
        }elseif($query['view']=='edititem'){
        	if ($view != $mView) {
				$segments[]=$par->get('seo_view_edititem','edititem');
	        }								
			if(isset($query['id'])){
				$segments[] = $query['id'];
				unset($query['id']);
			}				
		}elseif($query['view']=='additem'){
			if ($view != $mView) {
				$segments[]=$par->get('seo_view_additem','additem');
	        }										
		}elseif($query['view']=='useritems'){
			if ($view != $mView) {
				$segments[]=$par->get('seo_view_useritems','useritems');
	        }								
		}elseif($query['view']=='categories'){
			if ($view != $mView) {
				$segments[]=$par->get('seo_view_categories','categories');
	        }								
		}elseif($query['view']=='regions'){
			if ($view != $mView) {
				$segments[]=$par->get('seo_view_regions','regions');
	        }								
		}elseif($query['view']=='payment'){			
			$segments[]='payment';			
		}elseif($query['view']=='points'){
			if ($view != $mView) {			
				$segments[]='points';
			}			
		}elseif($query['view']=='userpoints'){
			if ($view != $mView) {			
				$segments[]='userpoints';
			}			
		}elseif($query['view']=='renewitem'){			
			$segments[]='renewitem';		
			if(isset($query['id'])){
				$segments[] = $query['id'];
				unset($query['id']);
			}	
		}else if ($view == 'profile') {        	        	
        	if ($view != $mView) {
        		$segments[]=$par->get('seo_view_profile','profile');
        	}
        	if(isset($query['uid'])){
				$segments[] =DJClassifiedsSEO::getURLfromSlug($uid);
            } 
            unset($query['uid']);			
        }elseif($query['view']=='profileedit'){
        	if ($view != $mView) {
				$segments[]=$par->get('seo_view_profileedit','profileedit');
	        }														
		}elseif($query['view']=='contact'){			
			$segments[]='contact';		
			if(isset($query['id'])){
				$segments[] = $query['id'];
				unset($query['id']);
			}
			if(isset($query['bid'])){
				$segments[] = $query['bid'];
				unset($query['bid']);
			}				
		}elseif($query['view']=='checkout'){			
			$segments[]='checkout';		
			if(isset($query['cid'])){
				$segments[] = $query['cid'];
				unset($query['cid']);
			}	
			if(isset($query['item_id'])){
				$segments[] = $query['item_id'];
				unset($query['item_id']);
			}
			if(isset($query['quantity'])){
				$segments[] = $query['quantity'];
				unset($query['quantity']);
			}
		}elseif($query['view']=='registration'){	
			if ($view != $mView) {			
				$segments[]='registration';
			}						
			/*if(isset($query['layout'])){
				$segments[] = $query['layout'];
			}*/
					
		}elseif($query['view']=='userplans'){
			if ($view != $mView) {			
				$segments[]='userplans';
			}			
		}elseif($query['view']=='plans'){
			if ($view != $mView) {			
				$segments[]='plans';
			}			
		} else {
			$segments[] = $query['view'];
		}
		
		unset($query['view']);
		if($layout!=$mLayout && $layout){
        	$segments[]=$layout;
        }
		unset($query['layout']);
		
		if ($mCatid === null) {
        		//$mCatid = '0:all'; 
        }

		if($mView==$view && $mLayout ==  $layout && $mCatid == $cid && ($se || $order || $uid) && $menuDefault->id==$menuItem->id){			
			$segments[]='all'; 
		}
		
    }    
    
    return $segments;
}

function DJClassifiedsParseRoute($segments) {
	
	$app	= JFactory::getApplication();
	$menu	= $app->getMenu();
	$activemenu = $menu->getActive();	
	$par = JComponentHelper::getParams( 'com_djclassifieds' );
	
	$catalogViews = array($par->get('seo_view_item','ad'), 
						  $par->get('seo_view_items','ads'), 
						  $par->get('seo_view_edititem','edititem'),
						  $par->get('seo_view_additem','additem'),
						  $par->get('seo_view_useritems','useritems'),
						  $par->get('seo_view_categories','categories'),
						  $par->get('seo_view_profile','profile'),
						  $par->get('seo_view_peofileefit','profileedit'));
	
	$query=array();
	$temp=array();
	if (count($segments)) {

		//if (!in_array($segments[0], $catalogViews)) {
			$temp_view = '';
	            if ($activemenu) {
	                $temp=array();
	                $temp[0] = $activemenu->query['view'];
	                $temp_view = $activemenu->query['view'];
	                switch ($temp[0]) {
	                	case 'item' : {
	                        $temp[1] = @$activemenu->query['cid'];
							$temp[2] = @$activemenu->query['id'];
	                        /*foreach ($segments as $k=>$v) {
	                            $temp[$k+1] = $v;
	                        }*/
	                        
	                        break;
	                    }
	                    case 'items' : {
	                        $temp[1] = @$activemenu->query['cid'];
	                        $temp[2] = @$activemenu->query['rid'];
	                    	if(isset($activemenu->query['layout'])){
								$temp[3] = @$activemenu->query['layout'];	
							}
							
							
	                        /*foreach ($segments as $k=>$v) {
	                            $temp[$k+1] = $v;
	                        }*/
	                        break;
	                    }
	 					case 'edititem' : {
	                        $temp[1] = @$activemenu->query['id'];
	                        /*foreach ($segments as $k=>$v) {
	                            $temp[$k+1] = $v;
	                        }*/
	                        break;
	                    }
	                }
	                
	                //$segments = $temp;
	            }
	       // }
	       
	       
	       
		if (isset($segments[0])) { 
				if($segments[0]==str_ireplace('-', ':', $par->get('seo_view_item','ad')) || $segments[0]=='item') {
					$query['view']='item';
					if(isset($segments[3])){
						$query['id']=DJClassifiedsSEO::getIDfromURL($segments[3]);
						if(DJClassifiedsSEO::checkRegionURL($segments[2])){
							$query['rid']=DJClassifiedsSEO::getIDfromURL($segments[2]);
							$query['cid']=DJClassifiedsSEO::getIDfromURL($segments[1]);
						}else{
							$query['cid']=DJClassifiedsSEO::getIDfromURL($segments[2]);
							$query['rid']=DJClassifiedsSEO::getIDfromURL($segments[1]);
						}
						
						
					}else if(isset($segments[2])){
						$query['id']=DJClassifiedsSEO::getIDfromURL($segments[2]);
						if(isset($temp[1])){
							if($temp[0]=='items'){
								if($temp[1]==0){
									$query['cid']=DJClassifiedsSEO::getIDfromURL($segments[1]);
									$query['rid']=$temp[2];
								}else{
									$query['rid']=DJClassifiedsSEO::getIDfromURL($segments[1]);
									$query['cid']=$temp[1];
								}								
							}else{
								$query['cid']=DJClassifiedsSEO::getIDfromURL($segments[1]);
							}
						}else{
							$query['cid']=DJClassifiedsSEO::getIDfromURL($segments[1]);
						}
						
					}else{					
						if(isset($segments[1])){
							$query['id']=DJClassifiedsSEO::getIDfromURL($segments[1]);
						}
						if(isset($temp[1])){							
						   if($temp[0]=='items'){
						       $query['cid']=$temp[1];
						       $query['rid']=$temp[2];
						   }
						}
						
					} 
				}				
				else if($segments[0]==str_ireplace('-', ':', $par->get('seo_view_edititem','edititem')) || $segments[0]=='edititem') {
					$query['view'] = 'edititem';
					if (isset($segments[1])) {
						$query['id']=DJClassifiedsSEO::getIDfromURL($segments[1]);
					} 
				}
				else if($segments[0]==str_ireplace('-', ':', $par->get('seo_view_additem','additem')) || $segments[0]=='additem') {
					$query['view'] = 'additem';
				}
				else if($segments[0]==str_ireplace('-', ':', $par->get('seo_view_useritems','useritems')) || $segments[0]=='useritems') {
					$query['view'] = 'useritems';
				}
				else if($segments[0]==str_ireplace('-', ':', $par->get('seo_view_categories','categories')) || $segments[0]=='categories') {
					$query['view'] = 'categories';
				}
				else if($segments[0]==str_ireplace('-', ':', $par->get('seo_view_regions','regions')) || $segments[0]=='regions') {
					$query['view'] = 'regions';
				}
				else if($segments[0]=='payment') {
					$query['view'] = 'payment';
					if (isset($segments[1])) {
						$query['id']=DJClassifiedsSEO::getIDfromURL($segments[1]);
					} 
				}else if($segments[0]=='points') {
					$query['view'] = 'points';
				}else if($segments[0]=='userpoints') {
					$query['view'] = 'userpoints';
				}else if($segments[0]=='renewitem') {
					$query['view'] = 'renewitem';
					if (isset($segments[1])) {
						$query['id']=DJClassifiedsSEO::getIDfromURL($segments[1]);
					} 
				}else if($segments[0]==str_ireplace('-', ':', $par->get('seo_view_profile','profile')) || $segments[0]=='profile' || $temp_view == 'profile') {
					$query['view'] = 'profile';
					if (isset($segments[1])) {
						$query['uid']=DJClassifiedsSEO::getIDfromURL($segments[1]);
					}else{
						$query['uid']=DJClassifiedsSEO::getIDfromURL($segments[0]);
					} 
				}else if($segments[0]==str_ireplace('-', ':', $par->get('seo_view_profileedit','profileedit')) || $segments[0]=='profileedit') {
					$query['view'] = 'profileedit';					 
				}else if($segments[0]=='contact') {
					$query['view'] = 'contact';
					if (isset($segments[1])) {
						$query['id']=DJClassifiedsSEO::getIDfromURL($segments[1]);
					}
					if (isset($segments[2])) {
						$query['bid']=DJClassifiedsSEO::getIDfromURL($segments[2]);
					} 
				}else if($segments[0]=='checkout') {
					$query['view'] = 'checkout';
					if (isset($segments[1])) {
						$query['cid']=$segments[1];
					}
					if (isset($segments[2])) {
						$query['item_id']=DJClassifiedsSEO::getIDfromURL($segments[2]);
					}
					if (isset($segments[3])) {
						$query['quantity']=DJClassifiedsSEO::getIDfromURL($segments[3]);
					}
				}else if($segments[0]=='registration') {

					$query['view'] = 'registration';
					if (isset($segments[1])) {
						$query['layout']=$segments[1];
					}
				}else if($segments[0]=='plans') {
					$query['view'] = 'plans';
				}else if($segments[0]=='userplans') {
					$query['view'] = 'userplans';
				}else if($segments[0]==str_ireplace('-', ':', $par->get('seo_view_items','ads')) || $segments[0]=='items' || $temp_view=='items') {
					$query['view'] = 'items';
					if (isset($segments[1])) {
						if(DJClassifiedsSEO::isIDRegion($segments[1])){
							$query['rid']=DJClassifiedsSEO::getIDfromURL($segments[2]);
						}else{
							$query['cid']=DJClassifiedsSEO::getIDfromURL($segments[1]);
						}
					}
						
					if (isset($segments[2])) {
						if(DJClassifiedsSEO::isIDRegion($segments[2])){
							$query['rid']=DJClassifiedsSEO::getIDfromURL($segments[2]);
						}else{
							$query['cid']=DJClassifiedsSEO::getIDfromURL($segments[2]);
						}
					}					
					
					if($temp[0]=='items'){
						if(DJClassifiedsSEO::isIDRegion($segments[0])){
							$query['rid']=DJClassifiedsSEO::getIDfromURL($segments[0]);
						}else{
							$query['cid']=DJClassifiedsSEO::getIDfromURL($segments[0]);
						}
					}
					
					if ($temp_view=='items' && isset($temp[3])) {
						$query['layout'] = $temp[3];
					}
					
				}else if(isset($temp[1])){
				   if($temp[0]=='items'){
				   		$query['view'] = 'items';				   		
				   		if(DJClassifiedsSEO::isIDRegion($segments[0])){
				   			$query['rid']=DJClassifiedsSEO::getIDfromURL($segments[0]);
				   		}else{
				   			$query['cid']=DJClassifiedsSEO::getIDfromURL($segments[0]);
				   		}				   		
						if(isset($temp[2])){
							$query['layout'] = $temp[2];	
						}
				   }				   
				}else if(isset($temp[0])){
					if($temp[0]=='registration') {					
						$query['view'] = 'registration';
						if (isset($segments[0])) {
							$query['layout']=$segments[0];
						}
					} else {
						$query['view'] = $segments[0];
					}						
				}
				//print_r($query);die();
			
		}
	}
	
	return $query;
}
