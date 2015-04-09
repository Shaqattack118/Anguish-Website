var $ = new Neo({ enable_history: false });
var sliders = $.select("slider-container", "class");

sliders.each(function(slider) {

    var slides = slider.select("slide", "class");
    var actions = slider.select("figure", "tag");
    var first = slides.first();
    var offset = 0;
    var interval = setInterval(progress, 8000);

    actions.call("bind", [ "click", function(e) {

        e.preventDefault();

        var target = e.node;
        var index = 0;

        for(; index < actions.size(); index++) {
            if(actions.get(index) == target) {
                break;
            }
        }

        clearInterval(interval);
        interval = setInterval(progress, 8000);
        turn(index);

    } ]);

    function turn(index) {
        first.css("margin-left", -(first.width() * (offset = index))+"px");
        actions.call("removeClass", [ "active" ]);
        actions.get(index).addClass("active");
    }

    function progress() {

        if(offset + 1 >= slides.size()) {
            offset = -1;
        }

        turn(++offset);

    }

});