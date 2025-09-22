    document.addEventListener("DOMContentLoaded", () => {
        const favoriteButtons = document.querySelectorAll(".js-toggle-favorite");

        favoriteButtons.forEach(button => {
            button.addEventListener("click", (e) => {
                e.preventDefault(); // Prevent form fallback behavior

                const songId = button.dataset.songId;
                const title = button.dataset.title;
                const artist = button.dataset.artist;
                const songType = button.dataset.songType;
                const icon = button.querySelector("i");

                const formData = new URLSearchParams();
                formData.append("song_id", songId);
                formData.append("title", title);
                formData.append("artist", artist);
                formData.append("song_type", songType);

                fetch("add_favorite.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: formData.toString(),
                })
                .then(response => {
                    if (!response.ok) throw new Error("Request failed");
                    icon.classList.toggle("fas");
                    icon.classList.toggle("far");
                })
                .catch(err => {
                    console.error("Favorite toggle error:", err);
                });
            });
        });
    });
