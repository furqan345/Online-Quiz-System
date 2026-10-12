// ============================================================
// Quiz Timer Script
// Counts down and auto-submits the quiz form when time is up
// Usage: startTimer(durationInSeconds, "timerDisplayId", "quizFormId");
// ============================================================

function startTimer(duration, displayId, formId) {
    let timer = duration;
    let display = document.getElementById(displayId);

    let interval = setInterval(function () {
        let minutes = Math.floor(timer / 60);
        let seconds = timer % 60;

        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;

        display.textContent = minutes + ":" + seconds;

        if (timer <= 0) {
            clearInterval(interval);
            alert("Time is up! Submitting your quiz automatically.");
            document.getElementById(formId).submit();
        }
        timer--;
    }, 1000);
}
