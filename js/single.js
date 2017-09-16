/**
 * Created by Vitaly Kukin on 16.09.2017.
 */
jQuery(function($){

    $('.sku-set').on('click', function(){

        if( ! $(this).hasClass('active') ) {
            var t = $(this),
                d = t.parent();

            d.addClass('clicked').find('.active').removeClass('active');
            t.addClass('active');

            var p = t.parents('td'),
                s = p.find('select');

            s.val(t.data('value')).change();
        }
    });

    $( '.variations_form' ).on('check_variations', function(){

        var th = $(this);

        setTimeout(function(){
            th.find('.na-attribute-option').each(function(){

                if( ! $(this).hasClass('clicked') ) {
                    var v = [];

                    $(this).parent().find('select option').each(function(){
                        if( this.value !== '') v.push(this.value.toString());
                    });

                    $(this).find('.sku-set').each(function(){

                        var vv = $(this).data('value').toString();

                        if( $.inArray(vv, v) !== -1 ) {
                            $(this).css('display', '');
                        } else {
                            $(this).css('display', 'none').removeClass('active');
                        }
                    });
                } else {
                    $(this).removeClass('clicked')
                }
            });
        }, 200);

    });

    $( document ).on( 'click', 'a.reset_variations', function() {
        $(this).parents('form').find('.sku-set').removeClass('active').css('display', '');
    } );
});