<?php
session_start();
require_once 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the folder ID from the URL
$folder_id = isset($_GET['folder_id']) ? intval($_GET['folder_id']) : 0;

// Fetch user details
$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();
$user_query->close();

// Fetch folder details
$folder_query = $conn->prepare("SELECT folder_name FROM folders WHERE folder_id = ? AND user_id = ?");
$folder_query->bind_param("ii", $folder_id, $user_id);
$folder_query->execute();
$folder = $folder_query->get_result()->fetch_assoc();
$folder_name = $folder['folder_name'] ?? "Unknown Folder";
$folder_query->close();

// Fetch flashcards
$flashcards_query = $conn->prepare("SELECT * FROM flashcards WHERE folder_id = ? AND user_id = ?");
$flashcards_query->bind_param("ii", $folder_id, $user_id);
$flashcards_query->execute();
$flashcards = $flashcards_query->get_result()->fetch_all(MYSQLI_ASSOC);
$flashcards_query->close();

$total_flashcards = count($flashcards);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Flashcards - IskoCards</title>
    <link rel="stylesheet" href="../css/review.css">
</head>
<body>
    <div class="container">
        <!-- Folder Name & Flashcard Count -->
        <div class="top-section">
            <h1 class="folder-name"><?php echo htmlspecialchars($folder_name); ?></h1>
            <span class="flashcard-count" id="flashcard-counter">1/<?php echo $total_flashcards; ?></span>
        </div>

        <!-- Flashcard Display -->
        <div class="flashcard-display">
            <div class="flashcard-info">
                <span id="question-number" class="question-number">Question 1</span>
            </div>
            <div id="flashcard" class="flashcard">
                <div class="flashcard-inner">
                    <div class="flashcard-front">
                        <p id="flashcard-text">Click to flip</p>
                    </div>
                    <div class="flashcard-back">
                        <p id="flashcard-answer">Answer will appear here</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="navigation-buttons">
            <button id="prev-btn" class="nav-btn" onclick="prevFlashcard()">←</button>
            <button id="next-btn" class="nav-btn" onclick="nextFlashcard()">→</button>
        </div>

        <div class="elapsed-time">
        <span>Elapsed Time: </span>
            <span id="elapsed-time">00:00:00</span>
        </div>

        <!-- End Review Button -->
        <div class="end-review-btn">
            <button onclick="endReview()">End Review</button>
            </div>
        </div>

        <div id="end-review-modal" class="modal">
            <div class="modal-content">
                <h2>Nice Work!</h2>
                <p id="review-time-message"></p>
                <button onclick="closeEndReviewModal()">OK</button>
            </div>
        </div>


    <script>   
        let currentCardIndex = 0;
        const flashcards = <?php echo json_encode($flashcards); ?>;
        const totalFlashcards = <?php echo $total_flashcards; ?>;
        
        // Function to flip the flashcard
        function flipCard() {
            const flashcard = document.getElementById('flashcard');
            flashcard.classList.toggle('flipped');
        }

        // Function to show next flashcard
        function nextFlashcard() {
            if (currentCardIndex < totalFlashcards - 1) {
                currentCardIndex++;

                // Add the slide-left animation class for next
                const flashcard = document.getElementById('flashcard');
                flashcard.classList.add('flashcard-next');

                // Update flashcard display after the animation completes
                setTimeout(() => {
                    updateFlashcardDisplay();
                    flashcard.classList.remove('flashcard-next'); // Remove the animation class after the transition
                }, 800); // Duration of the animation (should match the CSS duration)
            }
        }

        // Function to show previous flashcard
        function prevFlashcard() {
            if (currentCardIndex > 0) {
                currentCardIndex--;

                // Add the slide-right animation class for previous
                const flashcard = document.getElementById('flashcard');
                flashcard.classList.add('flashcard-prev');

                // Update flashcard display after the animation completes
                setTimeout(() => {
                    updateFlashcardDisplay();
                    flashcard.classList.remove('flashcard-prev'); // Remove the animation class after the transition
                }, 800); // Duration of the animation (should match the CSS duration)
            }
        }


            // Function to update flashcard display
                function updateFlashcardDisplay() {
                const flashcard = document.getElementById('flashcard');
                const flashcardText = document.getElementById('flashcard-text');
                const flashcardAnswer = document.getElementById('flashcard-answer');
                const questionNumber = document.getElementById('question-number');
                const flashcardCounter = document.getElementById('flashcard-counter');

                // Reset the flashcard to show the question (front side)
                flashcard.classList.remove('flipped');

                // Update the question and answer
                const currentFlashcard = flashcards[currentCardIndex];
                flashcardText.innerHTML = currentFlashcard.question;
                flashcardAnswer.innerHTML = currentFlashcard.answer;

                // Update the question number and counter
                questionNumber.innerHTML = 'Question ' + (currentCardIndex + 1);
                flashcardCounter.innerHTML = (currentCardIndex + 1) + '/' + totalFlashcards;
            }


            // Function to end the review and go back to flashcards page
            function endReview() {
                window.location.href = 'flashcards.php?folder_id=<?php echo $folder_id; ?>';
            }

            // Initialize the first flashcard
            updateFlashcardDisplay();

            // Add event listener to flip card on click
            document.getElementById('flashcard').addEventListener('click', flipCard);

            // Elapsed time tracking
            let startTime = Date.now(); // Record the start time
            const elapsedTimeDisplay = document.getElementById('elapsed-time');

        function updateElapsedTime() {
            const currentTime = Date.now();
            const elapsedTime = Math.floor((currentTime - startTime) / 1000); // Elapsed time in seconds

            const hours = Math.floor(elapsedTime / 3600); // Calculate hours
            const minutes = Math.floor((elapsedTime % 3600) / 60); // Calculate minutes
            const seconds = elapsedTime % 60; // Calculate seconds

                // Format time as HH:MM:SS
                const formattedTime = 
                    String(hours).padStart(2, '0') + ':' + 
                    String(minutes).padStart(2, '0') + ':' + 
                    String(seconds).padStart(2, '0');

                elapsedTimeDisplay.textContent = formattedTime;
            }

        // Update the elapsed time every second
        setInterval(updateElapsedTime, 1000);

        // Function to end the review and show modal with elapsed time
        function endReview() {
            const currentTime = Date.now();
            const elapsedTime = Math.floor((currentTime - startTime) / 1000); // Elapsed time in seconds

            const hours = Math.floor(elapsedTime / 3600); // Calculate hours
            const minutes = Math.floor((elapsedTime % 3600) / 60); // Calculate minutes
            const seconds = elapsedTime % 60; // Calculate seconds

            // Format time as HH:MM:SS
            const formattedTime = 
                String(hours).padStart(2, '0') + ':' + 
                String(minutes).padStart(2, '0') + ':' + 
                String(seconds).padStart(2, '0');

            // Show the modal with the elapsed time
            const modal = document.getElementById('end-review-modal');
            const message = document.getElementById('review-time-message');
            message.textContent = `You have reviewed for ${formattedTime}.`;
            modal.classList.add('active');
        }

        function closeEndReviewModal() {
            const modal = document.getElementById('end-review-modal');
            modal.classList.remove('active');

            // Redirect to the flashcards page
            window.location.href = 'flashcards.php?folder_id=<?php echo $folder_id; ?>';
        }
    </script>
</body>
</html>
