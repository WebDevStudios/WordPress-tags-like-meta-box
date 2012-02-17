<?php

class Tags_Like_Meta_Box {

	function __construct( $args ) {
		if ( $args['ID'])
			return;
		$this->ID = $args['ID'];
		$this->labels->nice_name = $args['nice_name'] ? $args['nice_name']  : $args['ID'];
		$this->labels->checklist = $this->ID . "-checklist";
		$this->labels->div = $this->ID . "-div";
		$this->labels->ajaxtag = $this->ID . "-ajaxtag";
		$this->labels->textarea = $this->ID . "-textarea";
		$this->labels->JSObjectName = str_replace('-', '_', $this->ID );
		$this->labels->new = $this->ID . "-new";
		$this->labels->add_button = $this->ID . "-add_button";
		
		wp_enqueue_script('jquery');
		add_action('add_meta_boxes', array( $this, 'add_meta_box') );
		add_action('admin_head', array($this, 'add_style') );
		add_action('save_post', array($this, 'save_post') );
		add_action('admin_footer-post.php', array($this, 'jQuery'));
		add_action('admin_footer-post-new.php', array($this, 'jQuery'));
	}

	function add_style() {
		?><style>
		.<?php echo $this->labels->checklist; ?> span {
		margin-right: 25px;
		display: block;
		font-size: 11px;
		line-height: 1.8em;
		white-space: nowrap;
		cursor: default;
		}</style>
		<?php
	}
	function add_meta_box() {
		
		add_meta_box($this->ID, $this->labels->nice_name, array( $this, 'meta_box' ), null, 'side', 'core');	
	}

	function ajax_get_tags() {
		$id_split = explode( "-", $_POST['id']);
		echo get_post_meta($id_split[1], $this->ID, true);
		die;
	}

	function get_stored_data($post_id) {
		if ( get_post_meta($post_id, $this->ID, true) )
			echo get_post_meta($post_id, $this->ID, true);
	}

	function meta_box($post, $box) {
		global $post;
		if ( !isset($box['args']) || !is_array($box['args']) )
			$args = array();
		else
			$args = $box['args'];
		extract( wp_parse_args($args, $defaults), EXTR_SKIP );

		$disabled = !1 == 1 ? 'disabled="disabled"' : '';
		?>
		<div class="<?php echo $this->labels->div; ?>" id="<?php echo $this->ID; ?>">
			<div class="jaxtag">
			<div class="nojs-tags hide-if-js">
			<p>Text</p>
			<textarea name="<?php echo $this->labels->textarea; ?>" rows="3" cols="20" class="<?php echo $this->labels->textarea; ?>" id="<?php echo $this->labels->textarea; ?>" <?php echo $disabled; ?>><?php echo $this->get_stored_data( $post->ID ); // textarea_escaped by esc_attr() ?></textarea></div>
		 	<?php if ( 1 == 1 ) : ?>
			<div class="<?php echo $this->labels->ajaxtag; ?> hide-if-no-js">
				<label class="screen-reader-text" for="new-tag-<?php echo $tax_name; ?>"><?php echo $box['title']; ?></label>
				<p><input type="text" id="<?php echo $this->labels->new; ?>" name="<?php echo $this->labels->new; ?>" class="<?php echo $this->labels->new; ?> form-input-tip" size="16" autocomplete="off" value="" />
				<input type="button" class="button <?php echo $this->labels->add_button; ?>" value="<?php esc_attr_e('Add'); ?>" tabindex="3" /></p>
			</div>
			<?php endif; ?>
			</div>
			<div class="<?php echo $this->labels->checklist; ?>"></div>
		</div>
		<?php
	}
	function save_post($post_id) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      		return;
		if ( ! $_REQUEST[ $this->labels->textarea ] )
			return; 

		$data = esc_attr($_REQUEST[$this->labels->textarea]);
		update_post_meta($post_id, $this->ID, $data);
	}

	function jQuery() { ?>
		<script>(function($){

		<?php echo $this->labels->JSObjectName; ?> = {
			
			init : function() {

				var t = this, ajaxtag = $('div.<?php echo $this->labels->ajaxtag; ?>');
				
			    $('.<?php echo $this->labels->div; ?>').each( function() {
			        <?php echo $this->labels->JSObjectName; ?>.quickClicks(this);
			    });

				$('input.<?php echo $this->labels->add_button; ?>', ajaxtag).click(function(){
					t.flushTags( $(this).closest('.<?php echo $this->labels->div; ?>') );
				});

				$('input.<?php echo $this->labels->new; ?>', ajaxtag).keyup(function(e){
					if ( 13 == e.which ) {
						<?php echo $this->labels->JSObjectName; ?>.flushTags( $(this).closest('.<?php echo $this->labels->div; ?>') );
						return false;
					}
				}).keypress(function(e){
					if ( 13 == e.which ) {
						e.preventDefault();
						return false;
					}
				});

			    // save tags on post save/publish
			    $('#post').submit(function(){
					$('div.<?php echo $this->labels->div; ?>').each( function() {
			        	<?php echo $this->labels->JSObjectName; ?>.flushTags(this, false, 1);
					});
				});

			},

			clean : function(tags) {
				return tags.replace(/\s*,\s*/g, ';').replace(/,+/g, ';').replace(/[,\s]+$/, '').replace(/^[,\s]+/, '');
			},

			parseTags : function(el) {
				var id = el.id, num = id.split('-check-num-')[1], taxbox = $(el).closest('.<?php echo $this->labels->div; ?>'), thetags = taxbox.find('.<?php echo $this->labels->textarea; ?>'), current_tags = thetags.val().split(';'), new_tags = [];
				delete current_tags[num];
				// console.log(current_tags);

				$.each( current_tags, function(key, val) {
					val = $.trim(val);
					if ( val ) {
						new_tags.push(val);
					}
				});

				thetags.val( this.clean( new_tags.join(';') ) );

				this.quickClicks(taxbox);
				return false;
			},

			quickClicks : function(el) {
				var thetags = $('.<?php echo $this->labels->textarea; ?>', el),
					tagchecklist = $('.<?php echo $this->labels->checklist; ?>', el),
					id = $(el).attr('id'),
					current_tags, disabled;

				if ( !thetags.length )
					return;

				disabled = thetags.prop('disabled');

				current_tags = thetags.val().split(';');
				tagchecklist.empty();
				console.log(current_tags);
				$.each( current_tags, function( key, val ) {

					var span, xbutton;

					val = $.trim( val );

					if ( ! val )
						return;

					// Create a new span, and ensure the text is properly escaped.
					span = $('<span />').text( val );

					// If tags editing isn't disabled, create the X button.
					if ( ! disabled ) {
						xbutton = $( '<a id="' + id + '-check-num-' + key + '" class="ntdelbutton">X</a>' );
						xbutton.click( function(){ <?php echo $this->labels->JSObjectName; ?>.parseTags(this); });
						span.prepend('&nbsp;').prepend( xbutton );
					}

					// Append the span to the tag list.
					tagchecklist.append( span );
				});
			},

			//called on add tag, called on save
			flushTags : function(el, a, f) {
				a = a || false;
				var text, tags = $('.<?php echo $this->labels->textarea; ?>', el), newtag = $('input.<?php echo $this->labels->new; ?>', el), newtags;

				text = a ? $(a).text() : newtag.val();

				tagsval = tags.val();
				newtags = tagsval ? tagsval + ';' + text : text;

				newtags = this.clean( newtags );

				newtags = array_unique_noempty( newtags.split(';') ).join(';');

				tags.val(newtags);
				// console.log(newtags);
				this.quickClicks(el);

				if ( !a )
					newtag.val('');
				if ( 'undefined' == typeof(f) )
					newtag.focus();

				return false;
			}

		}
		})(jQuery);

		jQuery(document).ready(function() {
			<?php echo $this->labels->JSObjectName; ?>.init();
		});
		</script> <?php
	}
}

?>