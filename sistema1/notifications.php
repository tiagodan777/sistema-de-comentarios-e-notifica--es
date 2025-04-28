<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificações em Tempo Real</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2 class="text-center mb-4">Notificações em Tempo Real</h2>
    <div class="card shadow-sm p-4">
        <div class="text-end">
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Notifications <span id="unreadCount" class="badge bg-danger">0</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" id="notificationList">
                    <li><a href="" class="dropdown-item text-center">Não há novas notificações</a></li>
                </ul>
            </div>
        </div>
    </div>
    <script>
        let conn = new WebSocket('ws://localhost:8080')
        conn.onopen = function () {
            console.log('WebSocket Conectado!')
        }

        conn.onmessage = function(event) {
            let data = JSON.parse(event.data)
            if (data.type === 'notification') {
                updateNotificationDropdown(data.notifications)
            }
        }

        function updateNotificationDropdown(notifications) {
            let notificationList = window.document.getElementById('notificationList')
            let unreadBadge = window.document.getElementById('unreadCount')

            notificationList.innerHTML = ''

            if (notifications.lenght === 0) {
                notificationList.innerHTML = `<li><a class="dropdown-item text-center">Não há notificações novas</a></li>`
                unreadBadge.style.display = 'none'
                return
            }

            let count = 0

            notifications.forEach(function(notification) {
                let li = window.document.createElement('li')
                let a = window.document.createElement('a')

                a.class = 'dropdown-item'
                a.href = '#'
                a.innerHTML = `<strong>${notification.comment_subject}</strong>:${notification.comment_text}`

                if (notification.comment_status === 0) {
                    a.style.fontWeight = 'bold'
                    count++
                }
            })

            unreadBadge.textContent = count
            unreadBadge.style.display = count > 0 ? "Inline" : "none"
        }
    </script>
</body>
</html>
<script src="https://cdn.jsdelivr.net/npm/bootscrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>