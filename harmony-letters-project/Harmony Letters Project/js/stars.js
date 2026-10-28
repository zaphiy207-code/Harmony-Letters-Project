function createStars(numberOfStars) {
    var sky = document.getElementById("stars");

    if (!sky) {
        return;
    }

    for (var i = 0; i < numberOfStars; i++) {
        var star = document.createElement("span");

        star.className = "sky-star";

        // Random position across the screen
        star.style.top = Math.random() * 100 + "%";
        star.style.left = Math.random() * 100 + "%";

        // Random star size
        var size = Math.random() * 2 + 1;
        star.style.width = size + "px";
        star.style.height = size + "px";

        star.style.setProperty(
            "--twinkle-duration",
            (Math.random() * 3 + 1.5) + "s"
        );

        star.style.animationDelay =
            (Math.random() * 4) + "s";

        sky.appendChild(star);
    }
}


createStars(400);