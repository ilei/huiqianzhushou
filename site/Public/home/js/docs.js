! function(a) {
	"use strict";
	a(function() {
		var b = a(window),
			c = a(document.body);
		c.scrollspy({
			target: ".docs-sidebar"
		}), b.on("load", function() {
			c.scrollspy("refresh")
		}), a(".docs-container [href=#]").click(function(a) {
			a.preventDefault()
		}), setTimeout(function() {
			var b = a(".docs-sidebar");
			b.affix({
				offset: {
					top: function() {
						var c = b.offset().top,
							d = parseInt(b.children(0).css("margin-top"), 10),
							e = a(".docs-nav").height();
						return this.top = c - e - d
					}
				}
			})
		})
	})
}(jQuery);