<?php
// Include database connection
require_once 'includes/db_connection.php';

// Fetch Privacy Policy content
$policy_sections = array();
$sql = "SELECT * FROM privacy_policy ORDER BY display_order ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $policy_sections[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy | James Polymers</title>

    <!--Tab Icon-->
    <link rel="icon" type="image/png" href="assets/img/tab_icon.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- AOS Link -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>

<body class="bg-gray-50">
    <!-- Header -->
    <?php include 'header.php'; ?>

    <!-- Chatbot Integration -->
    <?php include 'chatbot.php'; ?>

    <section class="py-20 bg-gradient-to-br from-gray-100 via-white to-gray-100">
        <div class="container mx-auto px-4 max-w-4xl">
            <div
                class="bg-white p-10 md:p-12 rounded-2xl shadow-2xl border border-gray-200"
                data-aos="fade-up" data-aos-duration="1000">
                <h1
                    class="text-4xl font-extrabold text-center text-gray-800 mb-10 underline decoration-primary decoration-4 underline-offset-8"
                    data-aos="fade-down" data-aos-delay="100">
                    Privacy Policy
                </h1>

                <?php foreach ($policy_sections as $index => $section): ?>
                    <div
                        class="mb-10"
                        data-aos="fade-up"
                        data-aos-delay="<?php echo 150 + ($index * 100); ?>">
                        <h2 class="text-2xl font-semibold text-primary mb-3 tracking-wide">
                            <?php echo htmlspecialchars($section['section_title']); ?>
                        </h2>

                        <!-- Render raw HTML content -->
                        <div class="text-gray-700 text-base leading-relaxed">
                            <?php echo nl2br(htmlspecialchars($section['section_content'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- AOS JS -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script src="includes/javascript/privacy-policy.js" defer></script>
</body>

</html>