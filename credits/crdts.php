<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credits - Website Development Team</title>
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
            padding: 40px 20px;
        }

        .credits-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 1200px;
            margin: 0 auto;
            padding: 50px 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 50px;
        }

        h1 {
            font-size: 2.8em;
            color: #333;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            font-size: 1.2em;
            margin-bottom: 20px;
        }

        .divider {
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            margin: 30px auto;
            border-radius: 2px;
        }

        .section {
            margin: 50px 0;
        }

        .section-title {
            font-size: 2em;
            color: #667eea;
            margin-bottom: 15px;
            font-weight: 600;
            text-align: center;
        }

        .batch-label {
            display: inline-block;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 0.9em;
            font-weight: 600;
            margin-bottom: 25px;
        }

        .tech-portfolio {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 35px;
            margin-bottom: 40px;
        }

        .tech-portfolio h3 {
            color: #333;
            font-size: 1.6em;
            margin-bottom: 25px;
            text-align: center;
        }

        .tech-stack {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            margin-bottom: 30px;
        }

        .tech-badge {
            background: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            color: #667eea;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            font-size: 0.95em;
        }

        .accomplishments {
            text-align: left;
            max-width: 900px;
            margin: 0 auto;
        }

        .accomplishment-item {
            background: white;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .accomplishment-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        .accomplishment-title {
            font-weight: 600;
            color: #333;
            font-size: 1.1em;
            margin-bottom: 5px;
        }

        .accomplishment-desc {
            color: #666;
            line-height: 1.6;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .team-member {
            padding: 25px;
            background: #f8f9fa;
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
        }

        .team-member:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .member-name {
            font-size: 1.3em;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .member-role {
            color: #667eea;
            font-size: 1em;
            font-weight: 600;
        }

        .acknowledgment {
            background: linear-gradient(135deg, #667eea15, #764ba215);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            margin: 40px 0;
        }

        .acknowledgment h3 {
            color: #333;
            font-size: 1.5em;
            margin-bottom: 15px;
        }

        .acknowledgment p {
            color: #666;
            line-height: 1.8;
            font-size: 1.05em;
        }

        .footer-text {
            margin-top: 50px;
            text-align: center;
            color: #999;
            font-size: 0.95em;
            padding-top: 30px;
            border-top: 1px solid #eee;
        }

        .year {
            color: #667eea;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 2em;
            }

            .section-title {
                font-size: 1.6em;
            }

            .credits-container {
                padding: 30px 20px;
            }

            .team-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="credits-container">
        <div class="header">
            <h1>Website Development Credits</h1>
            <p class="subtitle">A comprehensive portfolio of technical achievements and team contributions</p>
            <div class="divider"></div>
        </div>

        <!-- Technical Portfolio Section -->
        <div class="section">
            <h2 class="section-title">Technical Portfolio</h2>
            
            <div class="tech-portfolio">
                <h3>Technologies & Frameworks Utilized</h3>
                <div class="tech-stack">
                    <span class="tech-badge">HTML5</span>
                    <span class="tech-badge">CSS3</span>
                    <span class="tech-badge">Tailwind CSS</span>
                    <span class="tech-badge">JavaScript</span>
                    <span class="tech-badge">PHP</span>
                    <span class="tech-badge">Brevo API</span>
                    <span class="tech-badge">MySQL</span>
                </div>

                <div class="accomplishments">
                    <div class="accomplishment-item">
                        <div class="accomplishment-title">🎨 Enhanced User Interface & Design</div>
                        <div class="accomplishment-desc">
                            Implemented comprehensive layout improvements and modern design patterns across the entire website, ensuring a cohesive and visually appealing user experience with responsive design principles.
                        </div>
                    </div>

                    <div class="accomplishment-item">
                        <div class="accomplishment-title">✏️ Content Management & Optimization</div>
                        <div class="accomplishment-desc">
                            Refined and expanded website content to improve clarity, engagement, and search engine optimization, while maintaining consistency across all pages and sections.
                        </div>
                    </div>

                    <div class="accomplishment-item">
                        <div class="accomplishment-title">📊 Dynamic Visitor Analytics System</div>
                        <div class="accomplishment-desc">
                            Developed and integrated a real-time visitor tracking system that monitors site traffic, providing valuable insights into user engagement and website performance metrics.
                        </div>
                    </div>

                    <div class="accomplishment-item">
                        <div class="accomplishment-title">📧 Email Integration via Brevo API</div>
                        <div class="accomplishment-desc">
                            Successfully implemented Brevo API integration to establish seamless email communication between the website and email servers, enabling automated notifications and contact form submissions.
                        </div>
                    </div>

                    <div class="accomplishment-item">
                        <div class="accomplishment-title">⚙️ Advanced Admin Dashboard Development</div>
                        <div class="accomplishment-desc">
                            Transformed the admin panel into a fully dynamic and interactive dashboard featuring real-time data visualization, enhanced user management capabilities, and streamlined content control systems.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Batch 3 Section -->
        <div class="section">
            <h2 class="section-title">Development Team</h2>
            
            <div style="text-align: center;">
                <span class="batch-label">Batch 3 - Previous Developer</span>
            </div>
            
            <div class="team-grid" style="max-width: 350px; margin: 30px auto;">
                <div class="team-member">
                    <div class="member-name">Daniel Ross B. Evia</div>
                    <div class="member-role">Lead Developer</div>
                </div>
            </div>

            <div style="margin: 50px 0;"></div>

            <div style="text-align: center;">
                <span class="batch-label">Batch 4 - Enhancement Team</span>
            </div>

            <div class="team-grid">
                <div class="team-member">
                    <div class="member-name">Francis Dave Basa</div>
                    <div class="member-role">Team Leader</div>
                </div>
                <div class="team-member">
                    <div class="member-name">Angelo G. Castillo</div>
                    <div class="member-role">Assistant Team Leader</div>
                </div>
                <div class="team-member">
                    <div class="member-name">Carl Joshua O. Tattao</div>
                    <div class="member-role">Developer</div>
                </div>
                <div class="team-member">
                    <div class="member-name">Riyan D. Mariano</div>
                    <div class="member-role">Developer</div>
                </div>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Acknowledgment Section -->
        <div class="acknowledgment">
            <h3>Special Acknowledgments</h3>
            <p>
                We extend our sincere gratitude to our instructors, mentors, and peers who provided invaluable guidance throughout this project. 
                Special thanks to the open-source community for the exceptional tools and frameworks that made this development possible. 
                This website represents countless hours of learning, collaboration, and dedication to creating a professional web solution.
            </p>
        </div>

        <div class="footer-text">
            <p><strong>Project Timeline:</strong> Batch 3 (Previous) → Batch 4 (Enhancement & Optimization)</p>
            <p style="margin-top: 10px;">© <span class="year"><?php echo date('Y'); ?></span> - Proudly developed and maintained by our dedicated team.</p>
        </div>
    </div>
</body>
</html>