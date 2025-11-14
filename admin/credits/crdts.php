<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credits - Website Team</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .credits-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 100%;
            padding: 50px 40px;
            text-align: center;
        }

        h1 {
            font-size: 2.5em;
            color: #333;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            font-size: 1.1em;
            margin-bottom: 40px;
        }

        .team-section {
            margin: 40px 0;
        }

        .section-title {
            font-size: 1.5em;
            color: #667eea;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-top: 20px;
        }

        .team-member {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .team-member:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .member-name {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }

        .member-role {
            color: #667eea;
            font-size: 0.95em;
            font-weight: 500;
        }

        .footer-text {
            margin-top: 50px;
            color: #999;
            font-size: 0.9em;
        }

        .year {
            color: #667eea;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="credits-container">
        <h1>Website Credits</h1>
        <p class="subtitle">Made with passion and dedication</p>

        <div class="team-section">
            <h2 class="section-title">Development Team</h2>
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-name">Francis Dave Basa</div>
                    <div class="member-role">Leader</div>
                </div>
                <div class="team-member">
                    <div class="member-name">Angelo Castillo</div>
                    <div class="member-role">Sub Leader</div>
                </div>
                <div class="team-member">
                    <div class="member-name">Carl Joshua O. Tattao</div>
                    <div class="member-role">Member</div>
                </div>
                <div class="team-member">
                    <div class="member-name">Riyan D. Mariano</div>
                    <div class="member-role">Member</div>
                </div>
            </div>
        </div>

        <div class="footer-text">
            © <span class="year"><?php echo date('Y'); ?></span> - Proudly crafted by our team
        </div>
    </div>
</body>
</html>