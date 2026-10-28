
"use strict";

const stars = document.querySelectorAll(".star");
const ratingText = document.getElementById("ratingText");
const reviewForm = document.getElementById("songReviewForm");
const reviewMessage = document.getElementById("reviewMessage");

const songTitle = document.getElementById("reviewSongTitle");
const artistName = document.getElementById("reviewArtistName");

let selectedRating = 0;

const params = new URLSearchParams(window.location.search);
const song = params.get("song");
const artist = params.get("artist");

if (song) songTitle.textContent = song;
if (artist) artistName.textContent = artist;

const ratingDescriptions = {
    1: "1 star - Poor",
    2: "2 stars - Fair",
    3: "3 stars - Good",
    4: "4 stars - Very good",
    5: "5 stars - Excellent"
};

function updateStars(rating) {
    stars.forEach(star => {
        const value = Number(star.dataset.rating);
        star.classList.toggle("selected", value <= rating);
        star.setAttribute(
            "aria-pressed",
            String(value === rating)
        );
    });
}

stars.forEach(star => {
    star.addEventListener("click", () => {
        selectedRating = Number(star.dataset.rating);
        updateStars(selectedRating);
        ratingText.textContent = ratingDescriptions[selectedRating];
        reviewMessage.textContent = "";
    });
});

reviewForm.addEventListener("submit", async event => {
    event.preventDefault();

    const reviewText = document.getElementById("reviewText").value.trim();
    const category = document.getElementById("reviewCategory").value;
    const songId = document.getElementById("songId").value;
    const csrfToken = document.getElementById("csrfToken").value;

    if (!selectedRating) {
        reviewMessage.textContent =
            "Please select a rating from 1 to 5 stars.";
        return;
    }

    if (!reviewText) {
        reviewMessage.textContent =
            "Please write your review before submitting.";
        return;
    }

    reviewMessage.textContent = "Submitting your review...";

    const formData = new URLSearchParams({
        csrf_token: csrfToken,
        song_id: songId,
        rating: String(selectedRating),
        category: category,
        review_text: reviewText
    });

    try {
        const response = await fetch("api/create_review.php", {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: formData.toString()
        });

        const data = await response.json();

        if (response.status === 401) {
            reviewMessage.textContent = data.message;

            const signin = document.createElement("a");
            signin.href = data.signin;
            signin.textContent = " Sign in ";

            const account = document.createElement("a");
            account.href = data.create_account;
            account.textContent = " Create an account";

            reviewMessage.append(
 		   document.createElement("br"),
  			  signin,
		   document.createElement("br"),
			  account
		);
            return;
        }

        if (!response.ok) {
            throw new Error(data.message || "Could not save your review.");
        }

        reviewMessage.textContent =
            data.message + " Visit Community Reviews to see it.";

        reviewForm.reset();
        selectedRating = 0;
        updateStars(0);
        ratingText.textContent = "Select a rating from 1 to 5 stars.";

    } catch (error) {
        reviewMessage.textContent =
            error.message || "Unable to submit your review. Please try again.";
    }
});
