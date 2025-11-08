<?php
require_once 'page_config.php';
$config = get_page_config('manufacturingprocess');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manufacturing Process - JPMC</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('<?php echo isset($config["main_bg"]) && $config["main_bg"] ? $config["main_bg"] : "images/sustainability/jpmclogo.png"; ?>') center center / contain no-repeat fixed;
            opacity: 0.4;
            z-index: -1;
        }
    </style>
</head>

<body class="bg-gray-100">
    <?php include 'header.php'; ?>

    <!-- chatbot.php -->
    <?php include 'chatbot.php'; ?>

    <!-- Header Section -->
    <div class="w-full py-6 flex flex-col items-center justify-center relative" style="background: url('<?php echo $config['header_bg']; ?>') center center / cover no-repeat; min-height: 130px;">
        <div class="absolute inset-0" style="background: <?php echo $config['header_overlay']; ?>;"></div>
        <h1 class="relative text-white text-4xl font-bold tracking-wider z-10" style="letter-spacing:2px; text-shadow: 1px 1px 4px #222;"><?php echo $config['header_title']; ?></h1>
        <div class="absolute left-0 bottom-0 h-1 bg-red-500 rounded-full z-10" style="width: 300px;"></div>
    </div>

    <!-- Main Content: Side by Side Badges and Coming Soon -->
    <div class="relative z-10 flex flex-row justify-center items-center py-16 gap-12" style="min-height: 60vh;">
        <!-- Left Badge -->
        <img
            src="<?php echo $config['left_badge']; ?>"
            alt="Best Award Badge"
            class="w-72 h-72 object-cover rounded-full" />
        <!-- Coming Soon -->
        <div class="flex items-center justify-center">
            <img src="<?php echo $config['coming_soon']; ?>" alt="Coming Soon" class="w-72 h-72 object-contain opacity-90" />
        </div>
        <!-- Right Badge -->
        <img
            src="<?php echo $config['right_badge']; ?>"
            alt="Best Award Badge"
            class="w-72 h-72 object-cover rounded-full" />
    </div>

    <?php include 'footer.php'; ?>
</body>

</html>