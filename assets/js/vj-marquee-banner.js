function vjMarqueeInitMarquee() {
    var marquees = document.querySelectorAll('.vj-marquee-marquee');

    marquees.forEach(function (marquee) {
        var track = marquee.querySelector('.vj-marquee-banner__track');
        var groups = marquee.querySelectorAll('.vj-marquee-banner__group');
        if (!track || groups.length < 2) {
            return;
        }

        var group = groups[0];
        var cloneGroup = groups[1];
        var items = Array.prototype.slice.call(group.children);
        if (!items.length) {
            return;
        }

        var containerWidth = marquee.offsetWidth;
        if (!containerWidth) {
            return;
        }

        var safety = 0;
        while (group.scrollWidth < containerWidth * 1.2 && safety < 20) {
            items.forEach(function (item) {
                group.appendChild(item.cloneNode(true));
            });
            safety++;
        }

        cloneGroup.innerHTML = group.innerHTML;
    });
}

document.addEventListener('DOMContentLoaded', vjMarqueeInitMarquee);
window.addEventListener('load', vjMarqueeInitMarquee);
