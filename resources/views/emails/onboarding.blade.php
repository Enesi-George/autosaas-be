<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Application Success</title>
        <style>
            @import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap");

            body {
                margin: 0;
                padding: 0;
                background-color: #f1f1f1;
                font-family: "Inter", sans-serif;
            }

            .email-container {
                max-width: 600px;
                margin: 20px auto;
                color: grey;
                padding: 30px;
                border-radius: 8px;
                line-height: 1.6;
            }

            h1 {
                font-size: 22px;
                font-weight: 700;
                margin-bottom: 10px;
                color: #16a34a;
            }

            p {
                font-size: 15px;
                margin-bottom: 16px;
            }

            .credentials {
                background: rgba(255, 255, 255, 0.08);
                padding: 15px;
                border-radius: 6px;
                margin: 10px 0;
            }

            .footer {
                text-align: center;
                margin-top: 30px;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            .footer img {
                max-width: 150px;
                opacity: 0.9;
            }
            .footer h1 {
                font-size: 24px;
                margin-left: 10px;
                color: #16a34a;
                font-weight: 800;
                letter-spacing: 2px;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <h1>Application Submitted Successfully!</h1>
            <p>
                Thank you for registering for the AUTOSAAS scholarship
                initiative first stage.
            </p>

            <div class="credentials">
                <p>
                    For the second stage, your login credentials are as follows:
                </p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Password:</strong> {{ $password }}</p>
            </div>

            <p>
                Please note that these credentials are unique to you and should
                be used to access the exams portal, the link to which will be
                communicated to you via email. Details regarding the exam date
                set for January 2026 and the code of conduct will also be shared
                through email.
            </p>
            <p>
                Kindly ensure you check your inbox or spam regularly or follow
                our social media platforms for timely updates.
            </p>

            <p>
                We wish you the best of luck in the upcoming exam and look
                forward to your continued participation in the AUTOSAAS
                scholarship initiative.
            </p>

            <p>Best regards,</p>
            <p>The AUTOSAAS Team</p>

            <div class="footer">
                <img src="cid:company-logo.png" alt="Autosaas-logo" />
                <h1>AUTOSAAS</h1>
            </div>
        </div>
    </body>
</html>