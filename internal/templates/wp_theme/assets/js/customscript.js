( function ( $ ) {
    // Menu fixes
    function onResizeMenuLayout() {
        if ( $( window ).width() > 767 ) {
            $( ".main-menu" ).on( 'hover', '.dropdown', function () {
                $( this ).addClass( 'open' )
            },
                function () {
                    $( this ).removeClass( 'open' )
                }
            );
            $( ".dropdown" ).on( 'focusin',
                function () {
                    $( this ).addClass( 'open' )
                }
            );
            $( ".dropdown" ).on( 'focusout',
                function () {
                    $( this ).removeClass( 'open' )
                }
            );

        } else {
            $( ".dropdown" ).on( 'hover',
                function () {
                    $( this ).removeClass( 'open' )
                }
            );
        }

        $( '#menu-categories-menu' ).on( 'focusout', function ( e ) {
            setTimeout( function () { // needed because nothing has focus during 'focusout'
                if ( $( ':focus' ).closest( '#menu-categories-menu' ).length <= 0 ) {
                    $( "#menu-categories-menu" ).removeClass( "open" );
                }
            }, 0 );
        } );
    }
    ;
    // initial state
    onResizeMenuLayout();
    // on resize
    $( window ).on( 'resize', onResizeMenuLayout );
    
    $( ".envo-categories-menu-first" ).on( 'click hover', function () {
        $( "#menu-categories-menu" ).toggleClass( "open" );
    } );

    $( ".main-menu" ).on( 'hover', '.navbar .dropdown-toggle', function () {
        $( this ).addClass( 'disabled' );
    } );
    $( '.navbar .dropdown-toggle' ).on( 'focus', function () {
        $( this ).addClass( 'disabled' );
    } );

    $( document ).ready( function () {
        var $myDiv = $( '#theme-menu' );
        $(".toggle").click(function(e){
            setTimeout(function(){ $('.nav-close-button').filter(':visible').focus(); }, 200);
            e.preventDefault();
        });
         
        if ( $myDiv.length ) {
            $('#theme-menu').hcOffcanvasNav({
                disableAt: 768,
                customToggle: $('.toggle'),
                levelTitles: false,
                levelTitleAsBack: false,
                pushContent: $('.page-wrap')
              });
        }
    } );

    $( 'form.cart' ).on( 'click', 'button.plus, button.minus', function () {
        // Get current quantity values
        var qty = $( this ).closest( 'form.cart' ).find( '.qty' );
        var val = parseFloat( qty.val() );
        var max = parseFloat( qty.attr( 'max' ) );
        var min = parseFloat( qty.attr( 'min' ) );
        var step = parseFloat( qty.attr( 'step' ) );

        // Change the value if plus or minus
        if ( $( this ).is( '.plus' ) ) {
            if ( max && ( max <= val ) ) {
                qty.val( max );
            } else {
                qty.val( val + step );
            }
        } else {
            if ( min && ( min >= val ) ) {
                qty.val( min );
            } else if ( val > 1 ) {
                qty.val( val - step );
            }
        }
    } );
    $( document ).ready( function () {
        $( '.cart-open .page-wrap' ).on( 'click', function () {
            $( "body" ).removeClass( "cart-open" );
        } );
        $( '.site-header-cart .la-times-circle' ).on( 'click', function () {
            $( "body" ).toggleClass( "cart-open" );
        } );
        $( '.header-cart' ).on( 'click', function () {
            $( "body" ).addClass( "cart-open" );
            $( ".site-header-cart .cart-close" ).focus();
        } );
    } );
    $( '.search-button' ).on( 'click', function ( e ) {
        $( ".header-search-field" ).appendTo( ".heading-row" );
        $( ".header-search-field" ).toggleClass( "visible hidden" );
        $( ".search-button .la" ).toggleClass( "la-times la-search" );
        $( ".header-search-field .search-field" ).focus();
    } );
    $( '.header-search-field' ).on( 'focusout', function ( e ) {
        setTimeout( function () { // needed because nothing has focus during 'focusout'
            if ( $( ':focus' ).closest( '.header-search-field' ).length <= 0 ) {
                $( ".header-search-field" ).removeClass( 'visible' ).addClass( 'hidden' );
                $( ".search-button .la" ).removeClass( 'la-times' ).addClass( 'la-search' );
                $( ".search-button" ).focus();
                $( ".header-search-field" ).appendTo( ".header-search" );
            }
        }, 0 );
    } );
    $( document ).ready( function () {
        $( "body" ).addClass( "js-loaded" );
    } );
    $(window).on('resize load', function() {
        if($(window).width() <= 767) {
            $('body').addClass('mobile');
            $('body').removeClass('desktop');
            $('body').removeClass('tablet');
        } else if ($(window).width() <= 991) {
            $('body').addClass('tablet');
            $('body').removeClass('mobile');
            $('body').removeClass('desktop');
        } else {
            $('body').addClass('desktop');
            $('body').removeClass('mobile');
            $('body').removeClass('tablet');
        }
        var $myDiv = $( 'body.transparent-header' );         
        if ( $myDiv.length ) {
            var height = $('.site-header').outerHeight();
            $('.transparent-header .top-header').css('top','-' + height + 'px');
        }
    });
} )( jQuery );