<?php
// Place this block anywhere in your dashboard page (preferably before </body>)
?>
<!-- Notification Bell Widget -->
<div style="position:fixed;top:20px;right:30px;z-index:1100;">
    <button type="button" class="btn btn-light position-relative" data-bs-toggle="modal" data-bs-target="#newEventsModal" id="notifBtn">
        <i class="bi bi-bell-fill"></i>
        <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;">0</span>
    </button>
</div>

<!-- Widget Modal for new events -->
<div class="modal fade" id="newEventsModal" tabindex="-1" aria-labelledby="newEventsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newEventsModalLabel"><i class="bi bi-bell"></i> New Event Notifications</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="notifModalBody">
        <div class="alert alert-success">No new event notifications.</div>
      </div>
    </div>
  </div>
</div>

<!-- Sound for notification -->
<audio id="notifSound" src="https://assets.mixkit.co/sfx/preview/mixkit-bell-notification-933.mp3" preload="auto"></audio>

<!-- Bootstrap JS for modal & AJAX -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
let lastNotifCount = 0;
function fetchNewEvents() {
    fetch('ajax_get_new_events.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('notifBadge');
            if (data.count > 0) {
                badge.innerText = data.count;
                badge.style.display = "inline";
                let bodyHtml = `<div class="alert alert-info">You have ${data.count} new event${data.count>1?'s':''}!</div>`;
                bodyHtml += '<ul class="list-group mb-2">';
                for (let evt of data.events) {
                    bodyHtml += `<li class="list-group-item">
                        <strong>${evt.title}</strong><br>
                        ${evt.description}<br>
                        <span class="text-muted"><i class="bi bi-calendar-event"></i> ${evt.event_date}</span>
                    </li>`;
                }
                bodyHtml += '</ul><a href="events.php" class="btn btn-primary btn-sm">View All Events</a>';
                document.getElementById('notifModalBody').innerHTML = bodyHtml;

                // Play sound only if new events detected since last check
                if (data.count > lastNotifCount) {
                    document.getElementById('notifSound').play();
                }
            } else {
                badge.style.display = "none";
                document.getElementById('notifModalBody').innerHTML = `<div class="alert alert-success">No new event notifications.</div>`;
            }
            lastNotifCount = data.count;
        });
}
// Initial fetch
fetchNewEvents();
// Poll every 30 seconds
setInterval(fetchNewEvents, 30000);
</script>