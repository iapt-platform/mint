/*************************************************************************
	(c) 2008-2012 Martin Wendt
 *************************************************************************/

/*******************************************************************************
 * jQuery.skinswitcher plugin.
 *
 * Change CSS include when combobox selection changes.
 * Copyright (c) 2012 Martin Wendt
 *
 * Usage:
	$("select#skinswitcher").skinswitcher({
		base: "../src/",
		choices: [{name: "XP", value: "xp", href: "skin/ui.fancytree.css"},
				  {name: "Vista", value: "vista", href: "skin-vista/ui.fancytree.css"},
				  {name: "Lion", value: "lion", href: "skin-lion/ui.fancytree.css"}
				  ],
		init: "lion"
	});
 */

	(function( $ ) {
		var PLUGIN_NAME = "skinswitcher",
			defaultOptions = {
				/**RegEx that returns prefix, tag, and suffix of the CSS href.*/
				skinPattern: "^(\W/skin-)().css$",
				mode: "combo", // {String} mode 'combo' or 'radio'
				base: "",
				choices: []
			},
			methods = {
				init: function(options) {
					var opts = $.extend({}, defaultOptions, options),
						hrefs = [],
						$link = null,
						initialChoice = undefined;
					this.data("options", opts);
					// Find <link> tag, figure out current setting and mark for
					// later access
					$.each(opts.choices, function(){
						hrefs.push(this.href.toLowerCase());
					});
					$("head link").each(function(){
						for(var i=0; i<hrefs.length; i++){
							if(this.href.toLowerCase().indexOf(hrefs[i]) >= 0){
								$link = $(this);
								$link.addClass(PLUGIN_NAME);
								initialChoice = opts.choices[i];
							}
						}
					});
					return this.each(function() {
						// Add options to dropdown list
						var $combo = $(this);
						$combo.empty();
						$.each(opts.choices, function(i, choice){
							var $opt = $("<option>", {
									text: choice.name,
									value: choice.value
								}).data("choice", choice);
							$combo.append($opt);
						});
						// Switch include
						$combo.change(function(){
							var choice = $(":selected", this).data("choice");
							$("link.skinswitcher").attr("href", opts.base + choice.href);
						});
						if(opts.init){
							$combo.val(opts.init).change();
						}else if (initialChoice){
							// select combobox value to match current <link> tag
							// decouple this call to prevent IE6 exception
							setTimeout(function(){
								$combo.val(initialChoice.value);
							}, 100);
						}
					});
				},
				option: function(name, value) {
					var opts = this.data("options");
					if(typeof value !== "undefined"){
						opts[name] = value;
					}else{
						return opts[name];
					}
				},
				change: function(href) {
					this.val("");
				},
				reset: function() {
					this.val("");
				}
			};
	
		$.fn[PLUGIN_NAME] = function(method) {
			// Method calling logic
			if ( methods[method] ) {
				return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
			} else if ( typeof method === "object" || ! method ) {
				return methods.init.apply(this, arguments);
			} else {
				$.error("Method " +  method + " does not exist on jQuery." + PLUGIN_NAME);
			}
		};
	})( jQuery );
	
	
	/**
	 * Replacement for $().toggle(func1, func2), which was deprecated with jQuery 1.8
	 * and removed in 1.9.;
	 * Taken from http://stackoverflow.com/a/4911660/19166
	 * By Felix Kling
	 */
	(function($) {
		$.fn.clickToggle = function(func1, func2) {
			var funcs = [func1, func2];
			this.data('toggleclicked', 0);
			this.click(function() {
				var data = $(this).data();
				var tc = data.toggleclicked;
				$.proxy(funcs[tc], this)();
				data.toggleclicked = (tc + 1) % 2;
			});
			return this;
		};
	}(jQuery));
	
	
	SAMPLE_BUTTON_DEFAULTS = {
		id: undefined,
		label: "Sample",
		newline: true,
		code: function(){ alert("not implemented"); }
	};
	function addSampleButton(options)
	{
		var opts = $.extend({}, SAMPLE_BUTTON_DEFAULTS, options),
			$container;
		$container = $("<span>", {
			"class": "sampleButtonContainer"
		});
		$("<button>", {
			id: opts.id,
			title: opts.tooltip,
			text: opts.label
		}).click(function(e){
			e.preventDefault();
			opts.code();
		}).appendTo($container);
	
		$("<a>", {
			text: "Source code",
			href: "#",
			"class": "showCode"
		}).appendTo($container)
		.click(function(e){
			try {
				prettyPrint();
			} catch (e) {
				alert(e);
			}
			var $pre = $container.find("pre");
			if($pre.is(":visible")){
				$(this).text("Source code");
			}else{
				$(this).text("Hide source");
			}
			$pre.toggle("slow");
			return false;
		});
		var sourceCode = "" + opts.code;
		// Remove outer function(){ CODE }
	//    sourceCode = sourceCode.match(/[]\{(.*)\}/);
		sourceCode = sourceCode.substring(
			sourceCode.indexOf("{") + 1,
			sourceCode.lastIndexOf("}"));
	//    sourceCode = $.trim(sourceCode);
		// Reduce tabs from 8 to 2 characters
		sourceCode = sourceCode.replace(/\t/g, "  ");
		// Format code samples
	
		$("<pre>", {
			text: sourceCode,
			"class": "prettyprint"
		}).hide().appendTo($container);
		if(opts.newline){
			$container.append($("<br>"));
		}
		if(opts.header){
			$("<h5>", {text: opts.header}).appendTo($("p#sampleButtons"));
		}
		$container.appendTo($("p#sampleButtons"));
	}
	
	
	function initCodeSamples()
	{
		var $source = $("#sourceCode");
		$("#codeExample").clickToggle(
			function(){
				$source.show("fast");
				if( !this.old ){
					this.old = $(this).html();
					$.get(this.href, function(code){
						// Remove <!-- Start_Exclude [...] End_Exclude --> blocks:
						code = code.replace(/<!-- Start_Exclude(.|\n|\r)*?End_Exclude -->/gi, "<!-- (Irrelevant source removed.) -->");
						// Reduce tabs from 8 to 2 characters
						code = code.replace(/\t/g, "  ");
						$source.text(code);
						// Format code samples
						try {
							prettyPrint();
						} catch (e) {
							alert(e);
						}
					}, "html");
				}
				$(this).html("Hide source code");
			},
			function(){
				$(this).html(this.old);
				$source.hide("fast");
			}
		);
		if(jQuery.ui){
			var info = "Fancytree " + jQuery.ui.fancytree.version
				+ ", jQuery UI " + jQuery.ui.version
				+ ", jQuery " + jQuery.fn.jquery;
	/*
			info += "\n<br>";
			info += "document.compatMode: " + document.compatMode + "\n";
			for(e in jQuery.support){
				info += "<br>\n" + e + ": " + jQuery.support[e];
			}
	*/
			$("p.sample-links").after("<p class='version-info'>" + info + "</p>");
		}
	}
	
	
	var _gaq = _gaq || [];
	
	$(function(){
		// Log to Google Analytics, when not running locally
		if ( document.URL.toLowerCase().indexOf("wwwendt.de/") >= 0 ) {
			_gaq.push(["_setAccount", "UA-316028-1"]);
			_gaq.push(["_trackPageview"]);
	
			(function() {
				var ga = document.createElement("script"); ga.type = "text/javascript"; ga.async = true;
				ga.src = ("https:" == document.location.protocol ? "https://ssl" : "http://www") + ".google-analytics.com/ga.js";
				var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ga, s);
			})();
		}
	
		// Show some elements only, if (not) inside the Example Browser
		if (top.location == self.location){
			$(".hideOutsideFS").hide();
		}else{
			$(".hideInsideFS").hide();
		}
		initCodeSamples();
	
		$("select#skinswitcher").skinswitcher({
			base: "../src/",
			choices: [{name: "XP", value: "xp", href: "skin-xp/ui.fancytree.css"},
					  {name: "Vista (classic Dynatree)", value: "vista", href: "skin-vista/ui.fancytree.css"},
					  {name: "Win7", value: "win7", href: "skin-win7/ui.fancytree.css"},
					  {name: "Win8", value: "win8", href: "skin-win8/ui.fancytree.css"},
					  {name: "Lion", value: "lion", href: "skin-lion/ui.fancytree.css"}
					  ]
	//		init: "lion"
		});
	
	});
	
	