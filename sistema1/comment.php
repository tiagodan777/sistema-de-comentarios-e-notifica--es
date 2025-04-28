<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Comentário</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2 class="text-center mb-4">Adicionar um Comentário</h2>
    <div class="card shadow-sm p-4">
        <form onsubmit="event.preventDefault(); submitComment();" method="post" id="commentForm">
            <div class="mb-3">
                <label for="" class="form-label">Assunto</label>
                <input type="text" name="" id="subject" class="form-control" require>
            </div>
            <div class="mb-3">
                <label for="" class="form-label">Comentário</label>
                <textarea name="" id="comment" class="form-control" rows="3" require></textarea>
            </div> 
            <button type="submit" class="btn btn-primary w-100">Adicionar Comentário</button>
        </form>
    </div>
    <script>
        let ws = new WebSocket("ws://localhost:8080")

        function submitComment() {
            let subject = window.document.getElementById('subject').value 
            let comment = window.document.getElementById('comment').value

            if (subject && comment) {
                let data = {
                    type : "new_comment",
                    subject : subject,
                    comment : comment
                }

                ws.send(JSON.stringify(data))

                window.document.getElementById('commentForm').reset()

                /*window.alert('Comentário Adicionado!')*/
            } else {
                window.alert('Todos os campos são obrigatórios')
            }
        }
    </script>
</body>
</html>