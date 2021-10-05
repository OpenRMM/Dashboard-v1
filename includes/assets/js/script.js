"use strict";
$(document).ready(function() {
    var $window = $(window);
    //add id to main menu for mobile menu start
    var getBody = $("body");
    var bodyClass = getBody[0].className;
    $(".main-menu").attr('id', bodyClass);
    //add id to main menu for mobile menu end

    // card js start
    $(".card-header-right .close-card").on('click', function() {
        var $this = $(this);
        $this.parents('.card').animate({
            'opacity': '0',
            '-webkit-transform': 'scale3d(.3, .3, .3)',
            'transform': 'scale3d(.3, .3, .3)'
        });

        setTimeout(function() {
            $this.parents('.card').remove();
        }, 800);
    });

    $(".card-header-right .minimize-card").on('click', function() {
        var $this = $(this);
        var port = $($this.parents('.card'));
        var card = $(port).children('.card-block').slideToggle();
        $(this).toggleClass("icon-minus").fadeIn('slow');
        $(this).toggleClass("icon-plus").fadeIn('slow');
    });
    $(".card-header-right .full-card").on('click', function() {
        var $this = $(this);
        var port = $($this.parents('.card'));
        port.toggleClass("full-card");
        $(this).toggleClass("icon-maximize");
        $(this).toggleClass("icon-minimize");
    });

    $("#more-details").on('click', function() {
        $(".more-details").slideToggle(500);
    });
    $(".mobile-options").on('click', function() {
        $(".navbar-container .nav-right").slideToggle('slow');
    });
    // card js end
    $.mCustomScrollbar.defaults.axis = "yx";
    $("#styleSelector .style-cont").slimScroll({
        setTop: "10px",
        height:"calc(100vh - 440px)",
    });
    $(".main-menu").mCustomScrollbar({
        setTop: "10px",
        setHeight: "calc(100% - 80px)",
    });
    /*chatbar js start*/

    /*chat box scroll*/
    var a = $(window).height() - 80;
    $(".main-friend-list").slimScroll({
        height: a,
        allowPageScroll: false,
        wheelStep: 5,
        color: '#1b8bf9'
    });

    // search
    $("#search-friends").on("keyup", function() {
        var g = $(this).val().toLowerCase();
        $(".userlist-box .media-body .chat-header").each(function() {
            var s = $(this).text().toLowerCase();
            $(this).closest('.userlist-box')[s.indexOf(g) !== -1 ? 'show' : 'hide']();
        });
    });

    // open chat box
    $('.displayChatbox').on('click', function() {
        var my_val = $('.pcoded').attr('vertical-placement');
        if (my_val == 'right') {
            var options = {
                direction: 'left'
            };
        } else {
            var options = {
                direction: 'right'
            };
        }
        $('.showChat').toggle('slide', options, 500);
    });


    //open friend chat
    $('.userlist-box').on('click', function() {
        var my_val = $('.pcoded').attr('vertical-placement');
        if (my_val == 'right') {
            var options = {
                direction: 'left'
            };
        } else {
            var options = {
                direction: 'right'
            };
        }
        $('.showChat_inner').toggle('slide', options, 500);
    });
    //back to main chatbar
    $('.back_chatBox').on('click', function() {
        var my_val = $('.pcoded').attr('vertical-placement');
        if (my_val == 'right') {
            var options = {
                direction: 'left'
            };
        } else {
            var options = {
                direction: 'right'
            };
        }
        $('.showChat_inner').toggle('slide', options, 500);
        $('.showChat').css('display', 'block');
    });
    // /*chatbar js end*/
    $(".search-btn").on('click', function() {
        $(".main-search").addClass('open');
        $('.main-search .form-control').animate({
            'width': '200px',
        });
    });
    $(".search-close").on('click', function() {
        $('.main-search .form-control').animate({
            'width': '0',
        });
        setTimeout(function() {
            $(".main-search").removeClass('open');
        }, 300);
    });
    $('#mobile-collapse i').addClass('icon-toggle-right');
    $('#mobile-collapse').on('click', function() {
        $('#mobile-collapse i').toggleClass('icon-toggle-right');
        $('#mobile-collapse i').toggleClass('icon-toggle-left');
    });
});
$(document).ready(function() {
    $(function() {
        $('[data-toggle="tooltip"]').tooltip()
    })
    $('.theme-loader').fadeOut('slow', function() {
        $(this).remove();
    });
});

// toggle full screen
function toggleFullScreen() {
    var a = $(window).height() - 10;
    if (!document.fullscreenElement && // alternative standard method
        !document.mozFullScreenElement && !document.webkitFullscreenElement) { // current working methods
        if (document.documentElement.requestFullscreen) {
            document.documentElement.requestFullscreen();
        } else if (document.documentElement.mozRequestFullScreen) {
            document.documentElement.mozRequestFullScreen();
        } else if (document.documentElement.webkitRequestFullscreen) {
            document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
        }
    } else {
        if (document.cancelFullScreen) {
            document.cancelFullScreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitCancelFullScreen) {
            document.webkitCancelFullScreen();
        }
    }
    $('.full-screen').toggleClass('icon-maximize');
    $('.full-screen').toggleClass('icon-minimize');
}

/* --------------------------------------------------------
        Color picker - demo only
        --------------------------------------------------------   */
$('#styleSelector').append('' +
    '<div class="selector-toggle">' +
        '<a href="javascript:void(0)"></a>' +
    '</div>' +
    '<ul>' +
        '<li>' +
            '<p class="selector-title main-title st-main-title"><b>Adminty </b>Customizer</p>' +
            '<span class="text-muted">Live customizer with tons of options</span>'+
        '</li>' +
        '<li>' +
            '<p class="selector-title">Main layouts</p>' +
        '</li>' +
        '<li>' +
            '<div class="theme-color">' +
                '<a href="#" class="navbar-theme" navbar-theme="themelight1"><span class="head"></span><span class="cont"></span></a>' +
                '<a href="#" class="navbar-theme" navbar-theme="theme1"><span class="head"></span><span class="cont"></span></a>' +
            '</div>' +
        '</li>' +
    '</ul>' +
    '<div class="style-cont m-t-10">' +
        '<ul class="nav nav-tabs  tabs" role="tablist">' +
            '<li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#sel-layout" role="tab">Layouts</a></li>' +
            '<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#sel-sidebar-setting" role="tab">Sidebar Settings</a></li>' +
        '</ul>' +
        '<div class="tab-content tabs">' +
            '<div class="tab-pane active" id="sel-layout" role="tabpanel">' +
                '<ul>' +
                    '<li class="theme-option">' +
                        '<div class="checkbox-fade fade-in-primary">' +
                            '<label>' +
                                '<input type="checkbox" value="false" id="sidebar-position" name="sidebar-position" checked>' +
                                '<span class="cr"><i class="cr-icon feather icon-check txt-success f-w-600"></i></span>' +
                                '<span>Fixed Sidebar Position</span>' +
                            '</label>' +
                        '</div>' +
                    '</li>' +
                    '<li class="theme-option">' +
                        '<div class="checkbox-fade fade-in-primary">' +
                            '<label>' +
                                '<input type="checkbox" value="false" id="header-position" name="header-position" checked>' +
                                '<span class="cr"><i class="cr-icon feather icon-check txt-success f-w-600"></i></span>' +
                                '<span>Fixed Header Position</span>' +
                            '</label>' +
                        '</div>' +
                    '</li>' +
                '</ul>' +
            '</div>' +
            '<div class="tab-pane" id="sel-sidebar-setting" role="tabpanel">' +
                '<ul>' +
                    '<li class="theme-option">' +
                        '<p class="sub-title drp-title">Menu Type</p>' +

                        '<div class="form-radio" id="menu-effect">'+
                            '<div class="radio radio-inverse radio-inline" data-toggle="tooltip" title="simple icon">'+
                                '<label>'+
                                    '<input type="radio" name="radio" value="st6" onclick="handlemenutype(this.value)" checked="true">'+
                                    '<i class="helper"></i><span class="micon st6"><i class="feather icon-command"></i></span>'+
                                '</label>'+
                            '</div>'+
                            '<div class="radio  radio-primary radio-inline" data-toggle="tooltip" title="color icon">'+
                                '<label>'+
                                    '<input type="radio" name="radio" value="st5" onclick="handlemenutype(this.value)">'+
                                    '<i class="helper"></i><span class="micon st5"><i class="feather icon-command"></i></span>'+
                                '</label>'+
                            '</div>'+
                        '</div>'+
                    '</li>' +
                    '<li class="theme-option">' +
                        '<p class="sub-title drp-title">SideBar Effect</p>' +
                        '<select id="vertical-menu-effect" class="form-control minimal">' +
                            '<option name="vertical-menu-effect" value="shrink">shrink</option>' +
                            '<option name="vertical-menu-effect" value="overlay">overlay</option>' +
                            '<option name="vertical-menu-effect" value="push">Push</option>' +
                        '</select>' +
                    '</li>' +
                    '<li class="theme-option">' +
                        '<p class="sub-title drp-title">Hide/Show Border</p>' +
                        '<select id="vertical-border-style" class="form-control minimal">' +
                            '<option name="vertical-border-style" value="solid">Style 1</option>' +
                            '<option name="vertical-border-style" value="dotted">Style 2</option>' +
                            '<option name="vertical-border-style" value="dashed">Style 3</option>' +
                            '<option name="vertical-border-style" value="none">No Border</option>' +
                        '</select>' +
                    '</li>' +
                    '<li class="theme-option">' +
                        '<p class="sub-title drp-title">Drop-Down Icon</p>' +
                        '<select id="vertical-dropdown-icon" class="form-control minimal">' +
                            '<option name="vertical-dropdown-icon" value="style1">Style 1</option>' +
                            '<option name="vertical-dropdown-icon" value="style2">style 2</option>' +
                            '<option name="vertical-dropdown-icon" value="style3">style 3</option>' +
                        '</select>' +
                    '</li>' +
                    '<li class="theme-option">' +
                        '<p class="sub-title drp-title">Sub Menu Drop-down Icon</p>' +
                        '<select id="vertical-subitem-icon" class="form-control minimal">' +
                            '<option name="vertical-subitem-icon" value="style1">Style 1</option>' +
                            '<option name="vertical-subitem-icon" value="style2">style 2</option>' +
                            '<option name="vertical-subitem-icon" value="style3">style 3</option>' +
                            '<option name="vertical-subitem-icon" value="style4">style 4</option>' +
                            '<option name="vertical-subitem-icon" value="style5">style 5</option>' +
                            '<option name="vertical-subitem-icon" value="style6">style 6</option>' +
                        '</select>' +
                    '</li>' +
                '</ul>' +
            '</div>' +
        '<ul>' +
            '<li>' +
                '<p class="selector-title">Header Brand color</p>' +
            '</li>' +
            '<li class="theme-option">' +
                '<div class="theme-color">' +
                    '<a href="#" class="logo-theme" logo-theme="theme1"><span class="head"></span><span class="cont"></span></a>' +
                    '<a href="#" class="logo-theme" logo-theme="theme2"><span class="head"></span><span class="cont"></span></a>' +
                    '<a href="#" class="logo-theme" logo-theme="theme3"><span class="head"></span><span class="cont"></span></a>' +
                    '<a href="#" class="logo-theme" logo-theme="theme4"><span class="head"></span><span class="cont"></span></a>' +
                    '<a href="#" class="logo-theme" logo-theme="theme5"><span class="head"></span><span class="cont"></span></a>' +
                '</div>' +
            '</li>' +
            '<li>' +
                '<p class="selector-title">Header color</p>' +
            '</li>' +
            '<li class="theme-option">' +
                '<div class="theme-color">' +
                    '<a href="#" class="header-theme" header-theme="theme1"><span class="head"></span><span class="cont"></span></a>' +
                    '<a href="#" class="header-theme" header-theme="theme2"><span class="head"></span><span class="cont"></span></a>' +
                    '<a href="#" class="header-theme" header-theme="theme3"><span class="head"></span><span class="cont"></span></a>' +
                    '<a href="#" class="header-theme" header-theme="theme4"><span class="head"></span><span class="cont"></span></a>' +
                    '<a href="#" class="header-theme" header-theme="theme5"><span class="head"></span><span class="cont"></span></a>' +
                    '<a href="#" class="header-theme" header-theme="theme6"><span class="head"></span><span class="cont"></span></a>' +
                '</div>' +
            '</li>' +
            '<li>' +
                '<p class="selector-title">Active link color</p>' +
            '</li>' +
            '<li class="theme-option">' +
                '<div class="theme-color">' +
                    '<a href="#" class="active-item-theme small" active-item-theme="theme1">&nbsp;</a>' +
                    '<a href="#" class="active-item-theme small" active-item-theme="theme2">&nbsp;</a>' +
                    '<a href="#" class="active-item-theme small" active-item-theme="theme3">&nbsp;</a>' +
                    '<a href="#" class="active-item-theme small" active-item-theme="theme4">&nbsp;</a>' +
                    '<a href="#" class="active-item-theme small" active-item-theme="theme5">&nbsp;</a>' +
                    '<a href="#" class="active-item-theme small" active-item-theme="theme6">&nbsp;</a>' +
                    '<a href="#" class="active-item-theme small" active-item-theme="theme7">&nbsp;</a>' +
                    '<a href="#" class="active-item-theme small" active-item-theme="theme8">&nbsp;</a>' +
                    '<a href="#" class="active-item-theme small" active-item-theme="theme9">&nbsp;</a>' +
                    '<a href="#" class="active-item-theme small" active-item-theme="theme10">&nbsp;</a>' +
                    '<a href="#" class="active-item-theme small" active-item-theme="theme11">&nbsp;</a>' +
                    '<a href="#" class="active-item-theme small" active-item-theme="theme12">&nbsp;</a>' +
                '</div>' +
            '</li>' +
            '<li>' +
                '<p class="selector-title">Menu Caption Color</p>' +
            '</li>' +
            '<li class="theme-option">' +
                '<div class="theme-color">' +
                    '<a href="#" class="leftheader-theme small" lheader-theme="theme1">&nbsp;</a>' +
                    '<a href="#" class="leftheader-theme small" lheader-theme="theme2">&nbsp;</a>' +
                    '<a href="#" class="leftheader-theme small" lheader-theme="theme3">&nbsp;</a>' +
                    '<a href="#" class="leftheader-theme small" lheader-theme="theme4">&nbsp;</a>' +
                    '<a href="#" class="leftheader-theme small" lheader-theme="theme5">&nbsp;</a>' +
                    '<a href="#" class="leftheader-theme small" lheader-theme="theme6">&nbsp;</a>' +
                '</div>' +
            '</li>' +
        '</ul>' +
    '</div>' +
'</div>' +
'');
/*

--------------------------------------------------------------------------
Code for link-hover text boxes
By Nicolas Höning
Usage: <a onmouseover="nhpup.popup('popup text' [, {'class': 'myclass', 'width': 300}])">a link</a>
The configuration dict with CSS class and width is optional - default is class .pup and width of 200px.
You can style the popup box via CSS, targeting its ID #pup. 
You can escape " in the popup text with &quot;.
Tutorial and support at http://nicolashoening.de?twocents&nr=8
--------------------------------------------------------------------------

The MIT License (MIT)

Copyright (c) 2014 Nicolas Höning

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

nhpup = {

    pup: null,      // This is the popup box, represented by a div    
    identifier: "pup",  // Name of ID and class of the popup box
    minMargin: 15,  // Set how much minimal space there should be (in pixels)
                    // between the popup and everything else (borders, mouse)
    default_width: 200, // Will be set to width from css in document.ready
    move: false,   // Move it around with the mouse? we are only ready for that when the mouse event is set up.
                   // Besides, having this turned off initially is resource-friendly.

    /*
     Write message, show popup w/ custom width if necessary,
      make sure it disappears on mouseout
    */
    popup: function(p_msg, p_config)
    {
        // do track mouse moves and update position 
        this.move = true;
        // restore defaults
        this.pup.removeClass()
                .addClass(this.identifier)
                .width(this.default_width);

        // custom configuration
        if (typeof p_config != 'undefined') {
            if ('class' in p_config) {
                this.pup.addClass(p_config['class']);
            }
            if ('width' in p_config) {
                this.pup.width(p_config['width']);
            }
        }

        // Write content and display
        this.pup.html(p_msg).show();

        // Make sure popup goes away on mouse out and we stop the constant 
        //  positioning on mouse moves.
        // The event obj needs to be gotten from the virtual 
        //  caller, since we use onmouseover='nhpup.popup(p_msg)' 
        var t = this.getTarget(arguments.callee.caller.arguments[0]);
        $jq(t).unbind('mouseout').bind('mouseout', 
            function(e){
                nhpup.pup.hide();
                nhpup.move = false;
            }
        );
    },

    // set the target element position
    setElementPos: function(x, y)
    {
        // Call nudge to avoid edge overflow. Important tweak: x+10, because if
        //  the popup is where the mouse is, the hoverOver/hoverOut events flicker
        var x_y = this.nudge(x + 10, y);
        // remember: the popup is still hidden
        this.pup.css('top', x_y[1] + 'px')
                .css('left', x_y[0] + 'px');
    },

    /* Avoid edge overflow */
    nudge: function(x,y)
    {
        var win = $jq(window);

        // When the mouse is too far on the right, put window to the left
        var xtreme = $jq(document).scrollLeft() + win.width() - this.pup.width() - this.minMargin;
        if(x > xtreme) {
            x -= this.pup.width() + 2 * this.minMargin;
        }
        x = this.max(x, 0);

        // When the mouse is too far down, move window up
        if((y + this.pup.height()) > (win.height() +  $jq(document).scrollTop())) {
            y -= this.pup.height() + this.minMargin;
        }

        return [ x, y ];
    },

    /* custom max */
    max: function(a,b)
    {
        if (a>b) return a;
        else return b;
    },

    /*
     Get the target (element) of an event.
     Inspired by quirksmode
    */
    getTarget: function(e)
    {
        var targ;
        if (!e) var e = window.event;
        if (e.target) targ = e.target;
        else if (e.srcElement) targ = e.srcElement;
        if (targ.nodeType == 3) // defeat Safari bug
            targ = targ.parentNode;
        return targ;
    },

    onTouchDevice: function() 
    {
        var deviceAgent = navigator.userAgent.toLowerCase();
        return deviceAgent.match(/(iphone|ipod|ipad|android|blackberry|iemobile|opera m(ob|in)i|vodafone)/) !== null;
    },
	
    initialized: false,
    initialize : function(){
        if (this.initialized) return;

        window.$jq = jQuery; // this is safe in WP installations with noConflict mode (which is default)

        /* Prepare popup and define the mouseover callback */
        jQuery(document).ready(function () {
            // create default popup on the page
            $jq('body').append('<div id="' + nhpup.identifier + '" class="' + nhpup.identifier + '" style="position:absolute; display:none; z-index:200;"></div>');
            nhpup.pup = $jq('#' + nhpup.identifier);

            // set dynamic coords when the mouse moves
            $jq(document).mousemove(function (e) {
                if (!nhpup.onTouchDevice()) { // turn off constant repositioning for touch devices (no use for this anyway)
                    if (nhpup.move) {
                        nhpup.setElementPos(e.pageX, e.pageY);
                    }
                }
            });
        });

        this.initialized = true;
    }
};

if ('jQuery' in window) nhpup.initialize();
