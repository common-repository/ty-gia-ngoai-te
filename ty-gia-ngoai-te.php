<?php
/*
Plugin Name: Tỷ Giá Ngoại Tệ
Plugin URI: http://wpvn.info/
Description: Widget tỷ giá ngoại tệ by wpvn.info
Version: 1.0
Author: wpvn
Author URI: http://wpvn.info/
License: GPLv2 or later
*/

function register_wpvn_ty_gia_ngoai_te_widget() {
    register_widget( 'WPVN_Ty_Gia_Ngoai_Te_Widget' );
}
add_action( 'widgets_init', 'register_wpvn_ty_gia_ngoai_te_widget' );

class WPVN_Ty_Gia_Ngoai_Te_Widget extends WP_Widget {

	public $maNT = array( 'AUD', 'CAD', 'CHF', 'DKK', 'EUR', 'GBP', 'HKD', 'INR', 'JPY', 'KRW', 'KWD', 'MYR', 'NOK', 'RUB', 'SAR', 'SEK', 'SGD','THB','USD' );

	public function __construct() {
		parent::__construct(
			'wpvn_ty_gia_ngoai_te_widget', // Base ID
			'Tỷ Giá Ngoại Tệ', // Name
			array( 'description' => 'Tỷ Giá Ngoại Tệ Widget by wpvn.info', ) // Args
		);
	}

	public function widget( $args, $instance ) {
		foreach ($this->maNT as $value) {
			$$value = ! empty( $instance[$value] ) ? '1' : '0';
		}
     	echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		?>
		<table id="wpvn-tgnt">
			<thead>
				<tr>
					<th title="Mã ngoại tệ">Mã NT</th>
					<th title="Mua tiền mặt">Mua TM</th>
					<th title="Mua chuyển khoản">Mua CK</th>
					<th>Bán</th>
				</tr>
			</thead>
			<tbody>
			<?php
			if ( false === ( $xml = get_transient( 'wpvn_ty_gia_ngoai_te' ) ) )
			{
				$xml = wp_remote_get('https://www.vietcombank.com.vn/ExchangeRates/ExrateXML.aspx');
				if ( $xml ) {
					$xml = trim($xml['body']);
					set_transient( 'wpvn_ty_gia_ngoai_te', $xml, 60*10 );
				} else {
					echo '<tr><td>Lỗi không lấy được dữ liệu!!!</td></tr></tbody></table>';
					exit();	
				}
			}
			$xml = new SimpleXMLElement($xml);
			foreach ($xml->Exrate as $result)
			{
				if ( $instance[trim($result['CurrencyCode'])] === 0 ) { ?>
				<tr>
					<td title="<?php echo $result['CurrencyName']; ?>"><?php echo $result['CurrencyCode']; ?></td>
					<td><?php echo number_format(intval($result['Buy']), 0, '.', ','); ?></td>
					<td><?php echo number_format(intval($result['Transfer']), 0, '.', ','); ?></td>
					<td><?php echo number_format(intval($result['Sell']), 0, '.', ','); ?></td>
				</tr>
				<?php }
			}
			?>
			</tbody>
		</table>
		<p>Tỷ giá VietComBank cập nhật lúc <?php echo $xml->DateTime; ?></p>
		<?php
		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		foreach ($this->maNT as $value) {
			$instance[$value] = !empty($new_instance[$value]) ? 1 : 0;
		}

		return $instance;
	}

	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : 'Tỷ Giá Ngoại Tệ';
		foreach ($this->maNT as $value) {
			$$value = isset($instance[$value]) ? (bool) $instance[$value] :false;
		} ?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
		<?php foreach ($this->maNT as $value) { ?>
			<input id="<?php echo $this->get_field_id( $value ); ?>" name="<?php echo $this->get_field_name( $value ); ?>"<?php checked( $$value ); ?> class="checkbox" type="checkbox">
			<label for="<?php echo $this->get_field_id( $value ); ?>">Ẩn tỷ giá <?php echo $value; ?></label>
			<br>
		<?php } ?>
		</p>
		<?php 
	}

}
