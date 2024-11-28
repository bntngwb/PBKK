const axios = require("axios");
const fs = require("fs");
const express = require("express");
const bodyParser = require("body-parser");
const FormData = require("form-data");

const app = express();
app.use(bodyParser.json({ limit: "10mb" }));  // Adjust the limit as needed

// Route to handle the image classification
app.post("/classify", async (req, res) => {
    try {
        // Get the base64-encoded image from the request body
        const { imageBase64 } = req.body;

        if (!imageBase64) {
            return res.status(400).json({ error: "No image provided in the request body" });
        }

        // Convert base64 to buffer and save to a temporary file
        const buffer = Buffer.from(imageBase64, "base64");
        const tempImagePath = "temp_image.jpg"; // Temporary file path
        fs.writeFileSync(tempImagePath, buffer); // Save the buffer as a file

        // Prepare form data for the Roboflow API
        const form = new FormData();
        form.append("image", fs.createReadStream(tempImagePath), {
            filename: "image.jpg",
            contentType: "image/jpeg"
        });

        // Send the image to the Roboflow API
        const response = await axios.post("https://classify.roboflow.com/image_classification-h3hoi/1", form, {
            headers: {
                ...form.getHeaders(),
                api_key: "HIFm3PCCz0AxbQeTvGKB"  // Replace with your Roboflow API key
            }
        });

        // Log the response for debugging
        console.log("Response from Roboflow:", response.data);

        // Clean up the temporary image file after the request
        fs.unlinkSync(tempImagePath);

        // Return the classification result
        res.json(response.data);
    } catch (error) {
        console.error("Error during classification:", error.message);

        // Return a detailed error message
        res.status(500).json({ error: error.message });
    }
});

// Set up the server
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
});
