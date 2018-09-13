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
* @Modyfied by  Paweł Stolarski
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

$app      = JFactory::getApplication();
$config   = JFactory::getConfig();
$document = JFactory::getDocument();

JHTML::_('behavior.calendar');

$today = JFactory::getDate()->format('Y-m-d');

?>

<article id="mod_djcf_stats<?php echo $module->id;?>" class="djcf_admin_stats_label">
  
  <section class="row-fluid">
  	
	  <form method="post">
	  	
	    <div class="btn-toolbar pull-right">
	    	
	    	<div class="btn-group radio pull-left">
	    		<input type="radio" id="date_format_d" name="date_format" value="d" <?php echo $date=='d'?'checked="checked"':'' ?>/>
	    		<label for="date_format_d" class="btn"><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_DAYS');?></label>
	    		<input type="radio" id="date_format_m" name="date_format" value="m" <?php echo $date=='m'?'checked="checked"':'' ?>/>
	    		<label for="date_format_m" class="btn"><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_MONTHS');?></label>
	    		<input type="radio" id="date_format_y" name="date_format" value="y" <?php echo $date=='y'?'checked="checked"':'' ?>/>
	    		<label for="date_format_y" class="btn"><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_YEARS');?></label>
	    	</div>
	    	
	    	<div class="btn-group pull-left">
	    		<?php echo JHtml::calendar($app->input->get('date_from'), 'date_from', 'date_from', '%Y-%m-%d', 'class="input-small" placeholder="'.JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_FROM_HINT').'"'); ?>
	    	</div>
	    	<div class="btn-group pull-left">
	    		<?php echo JHtml::calendar($app->input->get('date_to'), 'date_to', 'date_to', '%Y-%m-%d', 'class="input-small" placeholder="'.JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_TO_HINT').'"'); ?>
	    	</div>
	    	
	    	<div class="btn-group pull-left">
				<button type="submit" class="btn"><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_SHOW_BTN'); ?></button>
				<button type="button" class="btn" onclick="jQuery('#date_from').val(''); jQuery('#date_to').val(''); this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
			</div>
	    	
	    </div>	
	    
	  </form>
	  
  </section>
  
  <section class="row-fluid">
  	
    <div class="djcf_admin_stats_box djcf_admin_stats_graph" id="ads_summary_added">
      
      	<canvas id="ads_summary_added_canvas" height="200"></canvas>
      	
      	<script type="text/javascript">
		
			var chart = new Chart('ads_summary_added_canvas', {
				type: 'line',
				data: {
					labels: ["<?php echo implode('","', array_keys($arr_stats['ads_summary_added'])) ?>"],
					datasets: [
					{
						label: "<?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_CHART_ADDED') ?>",
						fill: true,
						lineTension: 0.1,
						backgroundColor: "rgba(75, 192, 192, 0.2)",
						borderColor: "rgba(75, 192, 192, 1)",
						pointRadius: 1,
						pointHoverRadius: 5,
						pointHitRadius: 10,
						data: [<?php echo implode(',', $arr_stats['ads_summary_added']) ?>],
					},
					{
						label: "<?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_CHART_EXPIRED') ?>",
						fill: true,
						lineTension: 0.1,
						backgroundColor: "rgba(255, 99, 132, 0.2)",
						borderColor: "rgba(255, 99, 132, 1)",
						pointRadius: 1,
						pointHoverRadius: 5,
						pointHitRadius: 10,
						data: [<?php echo implode(',', $arr_stats['ads_summary_expired']) ?>],
					}
					]
				},
				options: {
					maintainAspectRatio: false
				}
			});
		</script>
		
    </div>
    

    <div class="djcf_admin_stats_box djcf_admin_stats_graph" id="ads_summary_buy_now">
    	
      	<canvas id="ads_summary_buy_now_canvas" height="200"></canvas>
      
      	<script type="text/javascript">
		
			var chart = new Chart('ads_summary_buy_now_canvas', {
				type: 'line',
				data: {
					labels: ["<?php echo implode('","', array_keys($arr_stats['ads_summary_buy_now'])) ?>"],
					datasets: [
					{
						label: "<?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_CHART_BUY_NOW') ?>",
						fill: true,
						lineTension: 0.1,
						backgroundColor: "rgba(255, 159, 64, 0.2)",
						borderColor: "rgba(255, 159, 64, 1)",
						pointRadius: 1,
						pointHoverRadius: 5,
						pointHitRadius: 10,
						data: [<?php echo implode(',', $arr_stats['ads_summary_buy_now']) ?>],
					}
					]
				},
				options: {
					maintainAspectRatio: false
				}
			});
		</script>
		
    </div>
    

    <div class="djcf_admin_stats_box djcf_admin_stats_graph" id="ads_summary_profit">
      	
      	<canvas id="ads_summary_profit_canvas" height="200"></canvas>
      
		<script type="text/javascript">
		
			var chart = new Chart('ads_summary_profit_canvas', {
				type: 'line',
				data: {
					labels: ["<?php echo implode('","', array_keys($arr_stats['ads_summary_profit'])) ?>"],
					datasets: [
					{
						label: "<?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_CHART_PROFIT') ?>",
						fill: true,
						lineTension: 0.1,
						backgroundColor: "rgba(54, 162, 235, 0.2)",
						borderColor: "rgba(54, 162, 235, 1)",
						pointRadius: 1,
						pointHoverRadius: 5,
						pointHitRadius: 10,
						data: [<?php echo implode(',', $arr_stats['ads_summary_profit']) ?>],
					}
					]
				},
				options: {
					maintainAspectRatio: false
				}
			});
		</script>
	
    </div>
    
    <div class="eraser"></div>
  </section>
  <section class="row-fluid">
    <div class="djcf_admin_stats_box djcf_admin_stats_data">
      <h6><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_MOST_POPULAR_CATS') ?></h6>
      <p class="title">
      	<span><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_NAME') ?></span>
      	<span><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_ADS') ?></span>
      </p>
      <ul>
        <?php
          foreach($stats_list['categories'] as $v){;
            echo '<li><span>'. $v->name .'</span><span>'. $v->total .'</span></li>';
          }
        ?>
      </ul>
    </div>
    <div class="djcf_admin_stats_box djcf_admin_stats_data">
      <h6><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_MOST_POPULAR_PLANS') ?></h6>
      <p class="title">
      	<span><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_NAME') ?></span>
      	<span><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_SUBS') ?></span>
      </p>
      <ul>
        <?php
          foreach($stats_list['plans'] as $v){;
            echo '<li><span>'. $v->name .'</span><span>'. $v->total .'</span></li>';
          }
        ?>
      </ul>
    </div>
    <div class="djcf_admin_stats_box djcf_admin_stats_data">
      <h6><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_MOST_ADS_PER_USER') ?></h6>
      <p class="title">
      	<span><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_NAME') ?></span>
      	<span><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_ADS') ?></span>
      </p>
      <ul>
        <?php
          foreach($stats_list['ads_per_user'] as $v){;
            echo '<li>'
               .   '<span><a href="index.php?option=com_djclassifieds&task=profile.edit&id='. $v->id .'">'. $v->name .' ('. $v->id .')</a></span>'
               .   '<span>'. $v->total .'</span>'
               . '</li>'
               ;
          }
        ?>
      </ul>
    </div>
    <div class="djcf_admin_stats_box djcf_admin_stats_data">
      <h6><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_MOST_VIEWED_ADS') ?></h6>
      <p class="title">
      	<span><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_NAME') ?></span>
      	<span><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_VIEWS') ?></span>
      </p>
      <ul>
        <?php
          foreach($stats_list['ad_views'] as $v){;
            echo '<li>'
               .   '<span><a href="index.php?option=com_djclassifieds&task=item.edit&id='. $v->id .'">'. $v->name .'</a></span>'
               .   '<span>'. $v->total .'</span>'
               . '</li>'
               ;
          }
        ?>
      </ul>
    </div>
    <div class="eraser"></div>
  </section>
  
  <section class="row-fluid ">
    <h4><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_ADDED_BASIC'); ?>:</h4>

    <?php if($params->get('ads_total','1')){ ?>
      <div class="djcf_admin_stats_cell djcf_stats_ads_total">
        <span class="djcf_admin_stats_value"><?php echo $stats['ads_total']; ?></span>
        <p><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_ADS_TOTAL'); ?></p>
      </div>
    <?php } ?>

    <?php if($params->get('ads_active','1')){ ?>
      <div class="djcf_admin_stats_cell djcf_stats_ads_active">
        <span class="djcf_admin_stats_value"><?php echo $stats['ads_active']; ?></span>
        <p><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_ADS_ACTIVE'); ?></p>
      </div>
    <?php } ?>

    <?php if($params->get('auctions_count','1')){ ?>
      <div class="djcf_admin_stats_cell djcf_stats_auctions_count">
        <span class="djcf_admin_stats_value"><?php echo $stats['auctions_c']; ?></span>
        <p><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_AUCTIONS_COUNT'); ?></p>
      </div>
    <?php } ?>

    <?php if($params->get('cat_count','1')){ ?>
      <div class="djcf_admin_stats_cell djcf_stats_cat_count">
        <span class="djcf_admin_stats_value"><?php echo $stats['categories_c']; ?></span>
        <p><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_CATEGORIES_COUNT'); ?></p>
      </div>
    <?php } ?>	

    <div class="eraser"></div>
  </section>
  <section class="row-fluid">
    <h4><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_ADDED'); ?>:</h4>

    <?php if($params->get('ads_added_today','1')){ ?>
      <div class="djcf_admin_stats_cell djcf_stats_ads_added_today">
        <span class="djcf_admin_stats_value"><?php echo $stats['ads_today']; ?></span>
        <p><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_ADDED_TODAY'); ?></p>
      </div>
    <?php } ?>		

    <?php if($params->get('ads_added_1','1')){ ?>
      <div class="djcf_admin_stats_cell djcf_stats_ads_added_1d">
        <span class="djcf_admin_stats_value"><?php echo $stats['ads_1d']; ?></span>
        <p><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_ADDED_LAST_24H'); ?></p>
      </div>
    <?php } ?>	

    <?php if($params->get('ads_added_week','1')){ ?>
      <div class="djcf_admin_stats_cell djcf_stats_ads_added_week">
        <span class="djcf_admin_stats_value"><?php echo $stats['ads_week']; ?></span>
        <p><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_ADDED_CURRENT_WEEK'); ?></p>
      </div>
    <?php } ?>	

    <?php if($params->get('ads_added_7','1')){ ?>
      <div class="djcf_admin_stats_cell djcf_stats_ads_added_7d">
        <span class="djcf_admin_stats_value"><?php echo $stats['ads_7d']; ?></span>
        <p><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_ADDED_LAST_7_DAYS'); ?></p>
      </div>
    <?php } ?>	

    <?php if($params->get('ads_added_month','1')){ ?>
      <div class="djcf_admin_stats_cell djcf_stats_ads_added_month">
        <span class="djcf_admin_stats_value"><?php echo $stats['ads_month']; ?></span>
        <p><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_ADDED_CURRENT_MONTH'); ?></p>
      </div>
    <?php } ?>			

    <?php if($params->get('ads_added_30','1')){ ?>
      <div class="djcf_admin_stats_cell djcf_stats_ads_added_30">
        <span class="djcf_admin_stats_value"><?php echo $stats['ads_30d']; ?></span>
        <p><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_ADDED_LAST_30_DAYS'); ?></p>
      </div>
    <?php } ?>		

    <?php if($params->get('ads_added_year','1')){ ?>
      <div class="djcf_admin_stats_cell djcf_stats_ads_added_year">
        <span class="djcf_admin_stats_value"><?php echo $stats['ads_year']; ?></span>
        <p><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_ADDED_CURRENT_YEAR'); ?></p>
      </div>
    <?php } ?>							

    <?php if($params->get('ads_added_365','1')){ ?>
      <div class="djcf_admin_stats_cell djcf_stats_ads_added_365d">
        <span class="djcf_admin_stats_value"><?php echo $stats['ads_365d']; ?></span>
        <p><?php echo JText::_('MOD_DJCLASSIFIEDS_ADMIN_STATS_ADDED_LAST_365_DAYS'); ?></p>
      </div>
    <?php } ?>

    <div class="eraser"></div>
  </section>
</article>
