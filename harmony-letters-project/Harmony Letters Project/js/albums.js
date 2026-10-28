const albumCards = document.querySelectorAll(".album-card");

const title = document.getElementById("albumTitle");
const artist = document.getElementById("albumArtist");
const description = document.getElementById("albumDescription");
const reviewBtn = document.getElementById("reviewBtn");

const prevBtn = document.getElementById("prevBtn");
const nextBtn = document.getElementById("nextBtn");

let currentAlbum = 0;

function showAlbum(index) {
    currentAlbum =
        (index + albumCards.length) % albumCards.length;

    const selected = albumCards[currentAlbum];

    albumCards.forEach((card, i) => {
        card.classList.toggle("active", i === currentAlbum);
    });

    title.textContent = selected.dataset.title;
    artist.textContent = selected.dataset.artist;
    description.textContent = selected.dataset.description;
    reviewBtn.href = selected.dataset.review;
}

prevBtn.addEventListener("click", () => {
    showAlbum(currentAlbum - 1);
});

nextBtn.addEventListener("click", () => {
    showAlbum(currentAlbum + 1);
});

albumCards.forEach((card, index) => {
    card.addEventListener("click", () => {
        showAlbum(index);
    });
});

showAlbum(0);


const albumSearch = document.getElementById("albumSearch");
const searchBtn = document.getElementById("searchBtn");
const searchMessage = document.getElementById("searchMessage");

function searchAlbums() {
    const query = albumSearch.value.trim().toLowerCase();

    if (query === "") {
        searchMessage.textContent = "Please enter an album or artist name.";
        return;
    }

    const matchIndex = Array.from(albumCards).findIndex(card => {
        const albumTitle = card.dataset.title.toLowerCase();
        const albumArtist = card.dataset.artist.toLowerCase();

        return albumTitle.includes(query) ||
               albumArtist.includes(query);
    });

    if (matchIndex !== -1) {
        showAlbum(matchIndex);
        searchMessage.textContent =
            "Found: " + albumCards[matchIndex].dataset.title;
    } else {
        searchMessage.textContent =
            "No matching album found. Try another name.";
    }
}

searchBtn.addEventListener("click", searchAlbums);

albumSearch.addEventListener("keydown", event => {
    if (event.key === "Enter") {
        searchAlbums();
    }
});
