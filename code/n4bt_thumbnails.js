/*! DFOXT THUMBNAILS v0.0.1 ~ (c) 2016-2017 @快叫我韩大人 ~ http://nnnn.blog */
!(function (window, document, Math) {
	var DFOXT_Thumbnails = function(){
		// var dfoxt_wpmedia,element,options,attachment,imagebox,close,input,imagenum;
		this.Bind(this);
	}
	DFOXT_Thumbnails.prototype = {
		Bind:function(t){
			var element,imagebox,image,close,input,options;
			jQuery(document).on('click', '.dfoxt-upload', function(event) {
				// 定義
				element = jQuery(this).parents('#dfoxt-thumbnails');
				imagebox= jQuery(this);
				input 	= jQuery(element).find('input');
				close 	= jQuery(this).find('.dfoxt-close');
				// 獲取
				options = jQuery(element).data('options');

				var $name = t.createElemenuName();
				$name = wp.media({   
		            title: options.mediaform.title,   
		            button: {   
		                text: options.mediaform.botton,
		            },
		            multiple: options.multiple,
		            library : {
						type : 'image'
					}
		        });

		        $name.on('select',function(){   
					attachment = $name.state().get('selection').first().toJSON();
					if(!options.multiple){
						var img_w = jQuery(element).outerWidth(),img_h;
			            img_h = (img_w / attachment.width) * attachment.height;
			            if(img_h < jQuery(close).outerHeight()){
			            	img_h = jQuery(close).outerHeight();
			            }
			            jQuery(element).css({
			            	'height': img_h
			            });
			            jQuery(imagebox).css({
			            	'background-image': 'url('+attachment.url+')'
			            });
			            jQuery(input).val(attachment.id);
			            jQuery(close).addClass('active');
					}else{
						attachment = $name.state().get('selection').toJSON();
			            for (var i = 0; i < attachment.length; i++) {
			            	jQuery(imagebox).append('<div data-id="'+attachment[i].id+'" class="dfoxt-brick"><div class="dfoxt-block"><div class="dfoxt-bg" style="background-image:url('+attachment[i].url+')"></div></div><div class="dfoxt-close-min"><span class="dashicons dashicons-trash"></span></div></div>');
			            }
			            // 設置
			            var imgs = new Array();
			            jQuery(imagebox).find('.dfoxt-brick').each(function(index, el) {
			            	imgs.push(jQuery(this).data('id'));
			            });
			            jQuery(input).val(imgs.join(','));
						t.calcHeight(imagebox,element);
					}
		        });
		        $name.open();
			});
			jQuery(document).on('click', '.dfoxt-upload .dfoxt-close', function(event) {
				// 定義
				element = jQuery(this).parents('#dfoxt-thumbnails');
				imagebox= jQuery(element).find('.dfoxt-upload');
				close 	= jQuery(this);
				input 	= jQuery(element).find('input');
				// 獲取
				options = jQuery(element).data('options');
            	jQuery(element).removeAttr('style');
            	jQuery(imagebox).removeAttr('style');
            	jQuery(input).val('');
            	jQuery(this).removeClass('active');

            	jQuery(element).find('h2').show();
            	event.preventDefault();
            	event.stopPropagation();
			});
			jQuery(document).on('click', '.dfoxt-upload .dfoxt-close-min', function(event) {
				// 定義
				element = jQuery(this).parents('#dfoxt-thumbnails');
				imagebox= jQuery(this).parents('.dfoxt-upload');
				image 	= jQuery(this).parents('.dfoxt-brick');
				close 	= jQuery(this);
				input 	= jQuery(element).find('input');
				// 獲取
				options = jQuery(element).data('options');
				jQuery(image).remove();
				// 設置參數
				var imgs = new Array();
				jQuery(imagebox).find('.dfoxt-brick').each(function(index, el) {
					imgs.push(jQuery(this).data('id'));
				});
	            jQuery(input).val(imgs.join(','));

				t.calcHeight(imagebox,element);
            	event.preventDefault();
            	event.stopPropagation();
			});
			jQuery(window).resize(function(event) {
				setTimeout(function() {
					t.calcHeight(imagebox,element);
				}, 1000);
			});
		},
		Load:function(){
			t = this;
			jQuery(document).find('.dfoxt-upload').each(function(index, el) {
				// 定義
				element = jQuery(this).parents('#dfoxt-thumbnails');
				imagebox= jQuery(this);
				close 	= jQuery(this).find('.dfoxt-close');
				input 	= jQuery(element).find('input');
				// 獲取
				options = jQuery(element).data('options');
				attachment = jQuery(element).data('attachment');
				if(attachment == undefined){
					return true;﻿
				}
				if(!options.multiple){
					var img_w = jQuery(element).outerWidth(),img_h;
		            img_h = (img_w / attachment.width) * attachment.height;
		            if(img_h < jQuery(close).outerHeight()){
		            	img_h = jQuery(close).outerHeight();
		            }
		            jQuery(element).css({
		            	'height': img_h
		            });
		            jQuery(imagebox).css({
		            	'background-image': 'url('+attachment.url+')'
		            });
		            jQuery(close).addClass('active');

		            jQuery(element).find('h2').hide();
				}else{
					for (var i = 0; i < attachment.length; i++) {
						if(attachment[i] != null){
							jQuery(imagebox).append('<div data-id="'+attachment[i].id+'" class="dfoxt-brick"><div class="dfoxt-block"><div class="dfoxt-bg" style="background-image:url('+attachment[i].url+')"></div></div><div class="dfoxt-close-min"><span class="dashicons dashicons-trash"></span></div></div>');
						}
					}
			     	t.calcHeight(imagebox,element);
				}
			});
		},
		calcHeight:function(imagebox,element){
			var top = 0,h = 0;
            jQuery(imagebox).find('.dfoxt-brick').each(function(index, el) {
            	if(jQuery(this).offset().top > top){
            		h += jQuery(this).outerHeight();
            		top = jQuery(this).offset().top;
            	}
            });
            if(h == 0){
            	jQuery(element).find('h2').show();
            }else{
            	jQuery(element).find('h2').hide();
            }
            jQuery(element).css('height',h);
		},
		createElemenuName:function(){
		    var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';
		    var maxPos = $chars.length;
		    var elementName = '';
		    for (i = 0; i < 8; i++) {
		        elementName += $chars.charAt(Math.floor(Math.random() * maxPos));
		    }
		    return elementName;
		}
	}
	window.DFOXT_Thumbnails = DFOXT_Thumbnails;
})(window, document, Math);
// 添加适当的延时可能可以解决部分网络情况下出现的加载失败情况,测试所用,有问题请报告
jQuery(document).ready(function() {
	setTimeout(function() {
		var dfoxt =  new DFOXT_Thumbnails();
		dfoxt.Load();
	}, 500);
});