document.getElementById("authForm").addEventListener("submit", async function (e) {
    e.preventDefault(); 

    const playerName = document.getElementById("playerName").value;

    try {
        const response = await fetch("http://localhost:3000/auth", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ name: playerName })
        });

        if (!response.ok) {
            throw new Error("Failed to save user!");
        }

        const result = await response.json();
        document.getElementById("message").classList.remove("hidden");
        document.getElementById("playerDisplayName").textContent = playerName;


        document.getElementById("authForm").classList.add("hidden");
        console.log("User saved:", result);
    } catch (error) {
        console.error("Error:", error);
        alert("Failed to save user.");
    }
});
