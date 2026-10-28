
"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const reviewList = document.getElementById("reviewList");
    const reviewSearch = document.getElementById("reviewSearch");
    const songFilter = document.getElementById("songFilter");
    const categoryFilter = document.getElementById("categoryFilter");
    const sortFilter = document.getElementById("sortFilter");
    const reviewCount = document.getElementById("reviewCount");
    const emptyReviews = document.getElementById("emptyReviews");

    // These endpoints must be implemented in PHP.
    const API = {
        reviews: "api/get_reviews.php",
        vote: "api/vote_review.php"
    };

    let allReviews = [];
    let myVotes = {};
    let loading = false;

    if (!reviewList || !reviewSearch || !songFilter ||
        !categoryFilter || !sortFilter || !reviewCount ||
        !emptyReviews) {
        console.error("Review page is missing one or more required HTML elements.");
        return;
    }

    function makeElement(tag, className, text) {
        const element = document.createElement(tag);

        if (className) element.className = className;
        if (text !== undefined) element.textContent = text;

        return element;
    }

    function formatDate(value) {
        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return "Date unavailable";
        }

        return date.toLocaleDateString(undefined, {
            year: "numeric",
            month: "short",
            day: "numeric"
        });
    }

    async function fetchJSON(url, options = {}) {
        const response = await fetch(url, {
            credentials: "same-origin",
            ...options,
            headers: {
                "Accept": "application/json",
                ...(options.body
                    ? { "Content-Type": "application/json" }
                    : {}),
                ...options.headers
            }
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || "Request failed.");
        }

        return data;
    }

async function loadReviews() {
    loading = true;
    reviewList.replaceChildren();
    reviewList.append(
    makeElement("p", "", "Loading reviews...")
    );

        try {
            const data = await fetchJSON(API.reviews);

            if (!Array.isArray(data.reviews)) {
                throw new Error("The reviews API returned an unexpected response.");
            }

            allReviews = data.reviews.map(review => ({
                id: Number(review.id),
                username: review.username || "Anonymous",
                song: review.song || "Unknown song",
                artist: review.artist || "Unknown artist",
                rating: Number(review.rating) || 0,
                category: review.category || "General",
                text: review.text || "",
                date: review.date || "",
                helpful: Number(review.helpful) || 0,
                notHelpful: Number(review.notHelpful) || 0
            }));

const currentSong = songFilter.value;

const songs = [...new Set(
    allReviews.map(review => review.song).filter(Boolean)
)].sort((a, b) => a.localeCompare(b));

songFilter.replaceChildren();

const allSongsOption = document.createElement("option");
allSongsOption.value = "all";
allSongsOption.textContent = "All songs";
songFilter.append(allSongsOption);

songs.forEach(song => {
    const option = document.createElement("option");
    option.value = song;
    option.textContent = song;
    songFilter.append(option);
});

songFilter.value = songs.includes(currentSong)
    ? currentSong
    : "all";

            myVotes = data.myVotes || {};

            renderReviews();
        } catch (error) {
            console.error("Unable to load reviews:", error);
            reviewList.replaceChildren(
                makeElement(
                    "p",
                    "",
                    "Could not load reviews. Please try again."
                )
            );
            reviewCount.textContent = "Reviews unavailable";
            emptyReviews.hidden = true;
        } finally {
            loading = false;
        }
    }

    function createReviewCard(review) {
        const card = makeElement("article", "community-review-card");

        const top = makeElement("div", "community-review-top");
        top.append(
            makeElement(
                "p",
                "community-review-user",
                review.username
            ),
            makeElement(
                "time",
                "community-review-date",
                formatDate(review.date)
            )
        );

        card.append(top);

        const heading = makeElement(
            "h3",
            "community-review-song",
            review.song
        );

        card.append(
            heading,
            makeElement(
                "p",
                "community-review-artist",
                review.artist
            )
        );

        const rating = Math.max(0, Math.min(5, review.rating));

        card.append(
            makeElement(
                "p",
                "community-review-rating",
                `${"★".repeat(rating)}${"☆".repeat(5 - rating)} (${rating}/5)`
            ),
            makeElement(
                "span",
                "community-review-category",
                review.category
            ),
            makeElement(
                "p",
                "community-review-text",
                review.text
            )
        );

        const actions = makeElement(
            "div",
            "community-review-actions"
        );

        const currentVote = myVotes[review.id];

        const upButton = makeElement(
            "button",
            currentVote === "up" ? "vote-selected" : "",
            `👍 Helpful (${review.helpful})`
        );

        const downButton = makeElement(
            "button",
            currentVote === "down" ? "vote-down-selected" : "",
            `👎 Not helpful (${review.notHelpful})`
        );

        upButton.type = "button";
        downButton.type = "button";

        upButton.setAttribute("aria-pressed", String(currentVote === "up"));
        downButton.setAttribute("aria-pressed", String(currentVote === "down"));

        upButton.disabled = loading;
        downButton.disabled = loading;

        upButton.addEventListener("click", () => {
            submitVote(review.id, "up");
        });

        downButton.addEventListener("click", () => {
            submitVote(review.id, "down");
        });

        actions.append(
            upButton,
            downButton,
            makeElement(
                "span",
                "helpful-score",
                `Helpful score: ${review.helpful - review.notHelpful}`
            )
        );

        card.append(actions);
        return card;
    }

    function renderReviews() {
        const query = reviewSearch.value.trim().toLowerCase();
        const selectedSong = songFilter.value;
        const selectedCategory = categoryFilter.value;
        const sortBy = sortFilter.value;

        let reviews = allReviews.filter(review => {
            const searchableText = [
                review.song,
                review.artist,
                review.username,
                review.text,
                review.category
            ].join(" ").toLowerCase();

            return (
                searchableText.includes(query) &&
                (selectedSong === "all" || review.song === selectedSong) &&
                (selectedCategory === "all" ||
                    review.category === selectedCategory)
            );
        });

        if (sortBy === "newest") {
            reviews.sort(
                (a, b) => new Date(b.date) - new Date(a.date)
            );
        } else if (sortBy === "helpful") {
            reviews.sort(
                (a, b) =>
                    (b.helpful - b.notHelpful) -
                    (a.helpful - a.notHelpful)
            );
        } else if (sortBy === "rating") {
            reviews.sort((a, b) => b.rating - a.rating);
        }

        reviewList.replaceChildren();

        reviews.forEach(review => {
            reviewList.append(createReviewCard(review));
        });

        reviewCount.textContent =
            `${reviews.length} review${reviews.length === 1 ? "" : "s"} found`;

        emptyReviews.hidden = reviews.length !== 0;
    }

    async function submitVote(reviewId, vote) {
        try {
            await fetchJSON(API.vote, {
                method: "POST",
                body: JSON.stringify({
                    review_id: reviewId,
                    vote: vote
                })
            });

            // Reload database counts after the vote succeeds.
            await loadReviews();
        } catch (error) {
            console.error("Unable to save vote:", error);
            alert(error.message || "Could not save your vote. Please try again.");
        }
    }

    reviewSearch.addEventListener("input", renderReviews);
    songFilter.addEventListener("change", renderReviews);
    categoryFilter.addEventListener("change", renderReviews);
    sortFilter.addEventListener("change", renderReviews);

    loadReviews();
});
