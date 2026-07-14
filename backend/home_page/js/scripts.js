/*
* ----------------------------------------------------------------------------------------
Author       : wordpressboss
Template Name: Nista - Personal Portfolio Template
Version      : 1.0
* ----------------------------------------------------------------------------------------
*/

(function ($) {
    'use strict';

    jQuery(document).ready(function () {

        /*
         * ----------------------------------------------------------------------------------------
         *  PRELOADER JS
         * ----------------------------------------------------------------------------------------
         */
        $(window).on('load', function () {
            $('.loader').fadeOut();
            $('#preloader-area').delay(350).fadeOut('slow');

            $('.text-rotator').each(function () {

                var text_rotator_content = $(this).html();
                $(this).empty();
                $(this).html('<div class="rotator-wrap"></div>')
                var this_item = $(this).children('.rotator-wrap');
                var text_rotator_content_split = text_rotator_content.split(',');
                var item_size = text_rotator_content_split.length;
                nova_text_rotator(text_rotator_content_split, this_item, item_size);
            });

            function nova_text_rotator(item_array, this_item, item_size, my_index) {

                if (my_index == undefined)
                    var my_index = -1;

                my_index++

                if (my_index < item_size) {

                    this_item.fadeOut(800, function () {
                        this_item.html('<span>' + item_array[my_index] + '</span>');
                        this_item.fadeIn(800);

                    });
                } else {
                    my_index = -1;
                }

                setTimeout(function () {
                    nova_text_rotator(item_array, this_item, item_size, my_index);
                }, 2500);
            }
        });




        /*
         * ----------------------------------------------------------------------------------------
         *  CHANGE MENU BACKGROUND JS
         * ----------------------------------------------------------------------------------------
         */
        $(window).on('scroll', function () {
            if ($(window).scrollTop() > 200) {
                $('.header-top-area').addClass('menu-bg');
            } else {
                $('.header-top-area').removeClass('menu-bg');
            }
        });





        /*
         * ----------------------------------------------------------------------------------------
         *  SMOTH SCROOL JS
         * ----------------------------------------------------------------------------------------
         */

        $('a.smoth-scroll').on("click", function (e) {
            var anchor = $(this);
            $('html, body').stop().animate({
                scrollTop: $(anchor.attr('href')).offset().top - 50
            }, 1000);
            e.preventDefault();
        });


        /*
         * ----------------------------------------------------------------------------------------
         *  PARALLAX JS
         * ----------------------------------------------------------------------------------------
         */

        $(window).stellar({
            responsive: true,
            positionProperty: 'position',
            horizontalScrolling: false
        });



        /*
         * ----------------------------------------------------------------------------------------
         *  COUNTER UP JS
         * ----------------------------------------------------------------------------------------
         */

        $('.counter-num').counterUp();


        /*
         * ----------------------------------------------------------------------------------------
         *  WORK JS
         * ----------------------------------------------------------------------------------------
         */

        $('.work-inner').mixItUp();



        /*
         * ----------------------------------------------------------------------------------------
         *  MAGNIFIC POPUP JS
         * ----------------------------------------------------------------------------------------
         */

        var magnifPopup = function () {
            $('.work-popup').magnificPopup({
                type: 'image',
                removalDelay: 300,
                mainClass: 'mfp-with-zoom',
                gallery: {
                    enabled: true
                },
                zoom: {
                    enabled: true, // By default it's false, so don't forget to enable it

                    duration: 300, // duration of the effect, in milliseconds
                    easing: 'ease-in-out', // CSS transition easing function

                    // The "opener" function should return the element from which popup will be zoomed in
                    // and to which popup will be scaled down
                    // By defailt it looks for an image tag:
                    opener: function (openerElement) {
                        // openerElement is the element on which popup was initialized, in this case its <a> tag
                        // you don't need to add "opener" option if this code matches your needs, it's defailt one.
                        return openerElement.is('img') ? openerElement : openerElement.find('img');
                    }
                }
            });
        };
        // Call the functions 
        magnifPopup();





        /*
         * ----------------------------------------------------------------------------------------
         *  TESTIMONIAL JS
         * ----------------------------------------------------------------------------------------
         */

       $(document).ready(function(){
    $('.customer-logos').slick({
        slidesToShow: 6,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 1500,
        arrows: false,
        dots: false,
        pauseOnHover: false,
        responsive: [{
            breakpoint: 768,
            settings: {
                slidesToShow: 4
            }
        }, {
            breakpoint: 520,
            settings: {
                slidesToShow: 3
            }
        }]
    });
});


        /*
         * ----------------------------------------------------------------------------------------
         *  GOOGLE MAP JS
         * ----------------------------------------------------------------------------------------
         */
        var myCenter = new google.maps.LatLng(31.514923, 74.343897);

        function initialize() {
            var mapProp = {
                zoom: 18,
                center: myCenter,
                scrollwheel: false,
                styles: [{
                    "stylers": [{
                            "hue": "#ffffff"
                        }, {
                            saturation: -110
                        },
                        {
                            gamma: 2
                        }]
                }],

                mapTpeIdy: google.maps.MapTypeId.ROADMAP
            };
            var map = new google.maps.Map(document.getElementById("map"), mapProp);
            var measle = new google.maps.Marker({
                position: myCenter,
                map: map,
                icon: {
                    url: "https://maps.gstatic.com/intl/en_us/mapfiles/markers2/measle.png",
                    size: new google.maps.Size(7, 7),
                    anchor: new google.maps.Point(3.8, 3.8)
                }
            });
            new google.maps.Marker({
                position: myCenter,
                map: map,
                icon: {
                    url: "http://maps.google.com/mapfiles/ms/icons/red-dot.png",
                    labelOrigin: new google.maps.Point(115, 32),
                    size: new google.maps.Size(32, 32),
                    anchor: new google.maps.Point(16, 32)
                },
                label: {
                    text: "Neosoft Bussiness Solution",
                    color: "#C70E20",
                    fontWeight: "bold"
                }
            });
        }
        google.maps.event.addDomListener(window, 'load', initialize);

        /*
         * ----------------------------------------------------------------------------------------
         *  EXTRA JS
         * ----------------------------------------------------------------------------------------
         */
        $(document).on('click', '.navbar-collapse.in', function (e) {
            if ($(e.target).is('a') && $(e.target).attr('class') != 'dropdown-toggle') {
                $(this).collapse('hide');
            }
        });
        $('body').scrollspy({
            target: '.navbar-collapse',
            offset: 195
        });



        /*
         * ----------------------------------------------------------------------------------------
         *  WOW JS
         * ----------------------------------------------------------------------------------------
         */
        new WOW().init();







    });

})(jQuery);