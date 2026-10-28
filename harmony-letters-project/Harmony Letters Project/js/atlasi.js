
"use strict";

const vinylPlayer = document.querySelector(".vinyl-player");
const playBtn = document.getElementById("playBtn");

const songs = [
    { title: "Overture", youtubeId: "4RzhFZmY3Ik" },
    { title: "Woodwork", youtubeId: "SlWUvRfqDvM" },
    { title: "I'll Keep You Safe", youtubeId: "WQOojJm2KDw" },
    { title: "Bad Blood", youtubeId: "lEse0y9sGBc" },
    { title: "Uneven Odds", youtubeId: "6hJpZcW6pIA" },
    { title: "Light", youtubeId: "lJbscoDctuU" },
    { title: "You Are Enough", youtubeId: "wPZ29zcuZpU" },
    { title: "Heirloom", youtubeId: "auOivVnhI00" },
    { title: "The Projectionist", youtubeId: "EDLTyyKHzJs" },
    { title: "In the Embers", youtubeId: "yf6LeX6eABU" },

    { title: "Sun", youtubeId: "2nGd8MJ3Tb8" },
    { title: "Mercury", youtubeId: "pNUR0fOMpCs" },
    { title: "Venus", youtubeId: "2YbdBQpiaA4" },
    { title: "Earth", youtubeId: "gav_n33n_jI" },
    { title: "Moon", youtubeId: "CZOdB2Fxh18" },
    { title: "Mars", youtubeId: "MetLIUdKon8" },
    { title: "Jupiter", youtubeId: "Z0oEwoUSld8" },
    { title: "Saturn", youtubeId: "D_kNNC17nAU" },
    { title: "Uranus", youtubeId: "c7HXr719NFU" },
    { title: "Neptune", youtubeId: "Cxtsm4jLVjk" },
    { title: "Pluto", youtubeId: "noGzQhmVtAo" },

    { title: "North", youtubeId: "VrtMWD7rj1Y" },
    { title: "South", youtubeId: "3bzhZl5DmF0" },
    { title: "East", youtubeId: "iK0Aa1weZhI" },
    { title: "West", youtubeId: "JqceaH2nQ5I" },

    { title: "Pacific", youtubeId: "0An_yUObwO0" },
    { title: "Atlantic", youtubeId: "djFRLyWgRjM" },
    { title: "Indian", youtubeId: "KftrBr5FLAI" },
    { title: "Southern", youtubeId: "v11QQrQeQjY" },
    { title: "Arctic", youtubeId: "yhDN2GHPvgA" }
];

let youtubePlayer = null;
let playerReady = false;
let currentSongIndex = -1;
let isPlaying = false;
let pendingPlay = false;

// loading youtube iframe and also api related

window.onYouTubeIframeAPIReady = function () {
    const playerElement = document.getElementById("youtubePlayer");

    if (!playerElement) {
        console.error(
            'YouTube player element "#youtubePlayer" was not found.'
        );
        return;
    }

    youtubePlayer = new YT.Player("youtubePlayer", {
        width: "320",
        height: "180",
        videoId: "",
        playerVars: {
            playsinline: 1
        },
        events: {
            onReady: handlePlayerReady,
            onStateChange: handlePlayerStateChange,
            onError: handlePlayerError
        }
    });
};

if (!document.querySelector('script[src="https://www.youtube.com/iframe_api"]')) {
    const youtubeScript = document.createElement("script");

    youtubeScript.src = "https://www.youtube.com/iframe_api";
    youtubeScript.async = true;

    document.head.appendChild(youtubeScript);
}

// youtube handlers

function handlePlayerReady() {
    playerReady = true;

    updateControls();

    // A track may have been selected before the API finished loading.
    if (pendingPlay && currentSongIndex >= 0) {
        playCurrentSong();
    }
}

function handlePlayerStateChange(event) {
    switch (event.data) {
        case YT.PlayerState.PLAYING:
            isPlaying = true;
            pendingPlay = false;
            setVinylPlaying(true);
            updateControls();
            break;

        case YT.PlayerState.PAUSED:
            isPlaying = false;
            setVinylPlaying(false);
            updateControls();
            break;

        case YT.PlayerState.ENDED:
            isPlaying = false;
            pendingPlay = false;
            setVinylPlaying(false);
            updateControls();
            break;

        case YT.PlayerState.CUED:
            isPlaying = false;
            setVinylPlaying(false);
            updateControls();
            break;

        case YT.PlayerState.BUFFERING:
            break;

        default:
            break;
    }
}

function handlePlayerError(event) {
    isPlaying = false;
    pendingPlay = false;

    setVinylPlaying(false);
    updateControls();

    const messages = {
        2: "The YouTube video ID is invalid.",
        5: "This video could not be played in the embedded player.",
        100: "This video is unavailable or has been removed.",
        101: "The video owner does not allow embedded playback.",
        150: "The video owner does not allow embedded playback.",
        153: "YouTube could not verify the request. Check that the page is hosted on a website and that the browser is sending a valid referrer."
    };

    const message =
        messages[event.data] ||
        "YouTube could not play this video. Error code: " + event.data;

    console.error("YouTube player error:", event.data, message);
    alert(message);
}

// the lyric section to match title 

function normalizeTitle(text) {
    return text
        .toLowerCase()
        .replace(/[^a-z0-9]/g, "");
}

function showLyrics(title) {
    const wantedTitle = normalizeTitle(title);
    const lyricsPanels = document.querySelectorAll(".lyrics-panel");

    lyricsPanels.forEach(function (panel) {
        const heading = panel.querySelector("h2");

        const panelTitle = heading
            ? normalizeTitle(heading.textContent)
            : "";

        const matches = panelTitle === wantedTitle;

        panel.classList.toggle("active", matches);
        panel.style.display = matches ? "block" : "none";
    });
}

// display for track and play button

function updateControls() {
    const trackButtons = document.querySelectorAll(".track-play");

    trackButtons.forEach(function (button) {
        const index = Number(button.dataset.song);
        const selected = index === currentSongIndex;

        button.textContent =
            selected && isPlaying ? "❚❚" : "▶";

        button.setAttribute(
            "aria-label",
            (selected && isPlaying ? "Pause " : "Play ") +
                (songs[index] ? songs[index].title : "song")
        );
    });

    if (playBtn) {
        playBtn.textContent = isPlaying ? "Pause" : "Play";
        playBtn.setAttribute(
            "aria-label",
            isPlaying ? "Pause current song" : "Play current song"
        );
    }
}

// vinly animation. spin go brr

function setVinylPlaying(playing) {
    if (vinylPlayer) {
        vinylPlayer.classList.toggle("playing", playing);
    }
}

// selecting and playing the track

function selectSong(index) {
    if (!Number.isInteger(index) || !songs[index]) {
        console.warn("Invalid track index:", index);
        return;
    }

    currentSongIndex = index;
    isPlaying = false;
    pendingPlay = true;

    showLyrics(songs[index].title);
    setVinylPlaying(false);
    updateControls();

    // Wait until YouTube is ready, if necessary.
    if (!playerReady || !youtubePlayer) {
        return;
    }

    playCurrentSong();
}

function playCurrentSong() {
    const song = songs[currentSongIndex];

    if (!song || !playerReady || !youtubePlayer) {
        return;
    }

    if (!song.youtubeId) {
        pendingPlay = false;

        alert('No YouTube video ID is configured for "' + song.title + '".');
        return;
    }

    pendingPlay = false;

    // Loads the selected video and starts playback.
    youtubePlayer.loadVideoById(song.youtubeId);
}

// pause and play button


function togglePlayback() {
    // Choose the first track if none has been selected.
    if (currentSongIndex === -1) {
        selectSong(0);
        return;
    }

    if (!playerReady || !youtubePlayer) {
        pendingPlay = true;
        return;
    }

    if (isPlaying) {
        youtubePlayer.pauseVideo();
    } else {
        pendingPlay = false;
        youtubePlayer.playVideo();
    }
}

// process of changing the track button 

document.querySelectorAll(".track-play").forEach(function (button) {
    button.addEventListener("click", function (event) {
        event.preventDefault();

        const index = Number(button.dataset.song);

        // pausing the current playing track
        if (index === currentSongIndex && isPlaying) {
            if (playerReady && youtubePlayer) {
                youtubePlayer.pauseVideo();
            }
            return;
        }

        // Changing tack and clicking twice a paused track will resume it
        if (index === currentSongIndex && playerReady && youtubePlayer) {
            youtubePlayer.playVideo();
            return;
        }

        selectSong(index);
    });
});

// The Play Button

if (playBtn) {
    playBtn.addEventListener("click", togglePlayback);
}

// What lyrics will be greeted with first opening

showLyrics("Overture");
updateControls();
setVinylPlaying(false);
