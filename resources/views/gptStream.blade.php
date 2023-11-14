<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <form id="form-question">
        @csrf
        <input name="question" />
        <button id="btn-submit-question" type="submit">
            <span class="text-white">Submit</span>
        </button>
    </form>
    <p id="result" class="..."></p>
</body>

</html>

{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
<script>
    const formQuestion = document.getElementById("form-question");

    if (formQuestion) handleSubmitQuestion(formQuestion);

    function handleSubmitQuestion(form) {
        form.addEventListener("submit", (e) => {

            e.preventDefault();
            const question = e.target.question.value;

            const token = e.target._token.value;
            const btn = document.getElementById("btn-submit-question");

            // e.target.question.value = "";
            let count = 0;
            const result = document.getElementById("result");

            if (!question) return;
            const data = {
                message: question
            };
            fetch("/api/callgptstream", {
                    headers: {
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-Token": token,
                    },
                    method: "POST",
                    body: JSON.stringify(data),
                })
                .then(async (res) => {
                    const reader = res.body.getReader();
                    const decoder = new TextDecoder();
                    let text = "";
                    while (true) {
                        const {
                            value,
                            done
                        } = await reader.read();
                        if (done) break;
                        text = decoder.decode(value, {
                            stream: true
                        });
                        result.textContent += text;
                    }

                    btn.innerHTML = `Submit`;
                })
                .catch((e) => {
                    console.error(e);
                });
        });
    }

</script>
