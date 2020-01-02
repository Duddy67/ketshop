<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// no direct access
defined('_JEXEC') or die;

    //echo '<pre>';
    //var_dump($this->shippings);
    //echo '</pre>';
?>

<div class="blog purchase">

<?php if(!empty($this->shippings)) : ?>

  <form action="index.php?option=com_ketshop&task=shipment.pay" method="post" id="ketshop_shipment">
    <table class="table product-row end-table">

    <?php echo JLayoutHelper::render('order.product_header', $this->shop_settings); ?>
    <?php echo JLayoutHelper::render('order.product_rows', array('products' => $this->products, 'settings' => $this->shop_settings, 'params' => $this->params)); ?>
    <?php echo JLayoutHelper::render('order.amounts', array('amounts' => $this->amounts, 'detailed_amounts' => $this->detailed_amounts, 'settings' => $this->shop_settings, 'params' => $this->params)); ?>

      <table class="table product-row end-table">
	<tr class="shipping-row-bgr font-bold">
	 <td colspan="4"><?php echo JText::_('COM_KETSHOP_SHIPPING_LABEL'); ?></td>
        </tr>
      <?php foreach($this->shippings as $i => $shipping) :
	      // The first radio button is checked by default. 
	      $checked = ($i == 0) ? 'checked' : ''; 
	?>
	<tr>
	  <td><input type="radio" name="shipping" <?php echo $checked; ?> value="<?php echo $shipping->id; ?>"></td>
	  <td><span class="product-name"><?php echo $shipping->name; ?></span></td>
	  <?php if(!empty($shipping->price_rules)) : ?>
	    <td>
	      <span class="striked-price small">
		<?php echo UtilityHelper::floatFormat($shipping->shipping_cost).' '.$this->shop_settings->currency; ?>
	      </span>
	    </td>
	  <?php endif; ?>
	  <td>
	    <span class="price-column">
	      <?php echo UtilityHelper::floatFormat($shipping->final_shipping_cost).' '.$this->shop_settings->currency; ?>
	    </span>
	  </td>
	</tr>
      <?php endforeach; ?>
      </table>
    </table>
  </form>

<?php else : ?>
<?php endif; ?>
</div>
