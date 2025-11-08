<!-- Contact Section -->
<section class="py-16 bg-primary text-white">
    <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Contact Information -->
            <div class="w-full lg:w-1/2">
                <h2 class="text-3xl md:text-4xl font-bold mb-6">Get in Touch</h2>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <i class="fas fa-map-marker-alt mt-1 mr-4"></i>
                        <div>
                            <h3 class="text-lg font-bold mb-1">Address</h3>
                            <p>016 Panapaan 2, Bacoor City, 4102, Cavite, Philippines</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-phone-alt mt-1 mr-4"></i>
                        <div>
                            <h3 class="text-lg font-bold mb-1">Phone</h3>
                            <p>+63 (2) 85298978</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-envelope mt-1 mr-4"></i>
                        <div>
                            <h3 class="text-lg font-bold mb-1">Email</h3>
                            <p>jamespro_asia@yahoo.com</p>
                            <p>jamespolymers.international@gmail.com</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-clock mt-1 mr-4"></i>
                        <div>
                            <h3 class="text-lg font-bold mb-1">Hours</h3>
                            <p>Monday-Friday: 8:00am - 5:00pm</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="w-full lg:w-1/2">
                <h2 class="text-3xl md:text-4xl font-bold mb-6">Send Us a Message</h2>
                <form class="space-y-4" action="send_message.php" method="POST">
                    <div class="flex flex-col md:flex-row md:gap-4">
                        <div class="w-full md:w-1/2 mb-4 md:mb-0">
                            <input type="text" name="name" class="w-full py-3 px-4 rounded-lg bg-white bg-opacity-10 border border-white border-opacity-20 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 placeholder-white placeholder-opacity-70" placeholder="Your Name" required>
                        </div>
                        <div class="w-full md:w-1/2">
                            <input type="email" name="email" class="w-full py-3 px-4 rounded-lg bg-white bg-opacity-10 border border-white border-opacity-20 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 placeholder-white placeholder-opacity-70" placeholder="Your Email" required>
                        </div>
                    </div>
                    <input type="text" name="subject" class="w-full py-3 px-4 rounded-lg bg-white bg-opacity-10 border border-white border-opacity-20 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 placeholder-white placeholder-opacity-70" placeholder="Subject">
                    <textarea name="message" class="w-full py-3 px-4 rounded-lg bg-white bg-opacity-10 border border-white border-opacity-20 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 placeholder-white placeholder-opacity-70 h-32" placeholder="Your Message" required></textarea>
                    <button type="submit" class="bg-white text-primary hover:bg-gray-100 font-bold py-3 px-8 rounded-lg transition duration-300">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">