<!-- resources/views/upload.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Your Code</title>
    <link rel="stylesheet" href="{{ asset('css/upload.css') }}">
</head>

<body>

    <div class="upload-container">
        <h2>Upload Your Image</h2>
        <p>Let us help you to choose your perfect <a href="{{ url('/') }}">HMNS</a> perfume :)</p>

        <div id="drop-area">
            <form class="my-form">
                <p>Drag a file here or <span class="browse-link" onclick="document.getElementById('fileElem').click()">browse</span> for a file to upload.</p>
                <input type="file" name="file" id="fileElem" accept="image/*" onchange="handleFiles(this.files)">
            </form>
        </div>

        <!-- Element to display classification results -->
        <div id="classification-result"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
 <!-- Define image paths using Blade's asset helper -->
 <script>
    const imagePaths = {
        orangePerfume: "{{ asset('images/product-4.jpg') }}",
        bananaPerfume: "{{ asset('images/product-2.jpg') }}",
        strawberryPerfume: "{{ asset('images/product-1.jpeg') }}"
    };
</script>

<script>
    function handleFiles(files) {
        const formData = new FormData();
        formData.append('file', files[0]);

        $.ajax({
            url: "{{ route('upload.image') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            success: function (data) {
                console.log("Full Response Data:", data); // Log the entire response

                if (data.success) {
                    alert(data.success);

                    if (data.classification && data.classification.predictions && data.classification.predictions.length > 0) {
                        const topResult = data.classification.predictions[0];
                        const classificationName = topResult.class.toLowerCase().trim();
                        const accuracy = (topResult.confidence * 100).toFixed(2); // Convert to percentage and format to 2 decimal places

                        console.log("Top Classification Name:", classificationName); // Log the classification name
                        console.log("Accuracy:", accuracy + "%"); // Log the accuracy percentage

                        let resultHtml;

                        // Check if accuracy is below 50%
                        if (accuracy < 65) {
                            resultHtml = `<div class="top-result-box low-accuracy">No element detected (Accuracy: ${accuracy}%)</div>`;
                        } else {
                            const recommendations = {
                                "apple": {
                                    name: "HMNS Farhampton",
                                    image: imagePaths.orangePerfume,
                                    accords: [
                                        { name: "amber", color: "#a0522d", width: "80%" },
                                        { name: "floral", color: "#ff69b4", width: "70%" },
                                        { name: "woody", color: "#8b4513", width: "60%" },
                                        { name: "sweet", color: "#ffb6c1", width: "50%" }
                                    ]
                                },
                                "orange": {
                                    name: "HMNS Alpha",
                                    image: imagePaths.bananaPerfume,
                                    accords: [
                                        { name: "citrus", color: "#ffd600", width: "85%" },
                                        { name: "green", color: "#32cd32", width: "75%" },
                                        { name: "tropical", color: "#ff6347", width: "65%" },
                                        { name: "woody", color: "#a0522d", width: "55%" },
                                        { name: "musky", color: "#696969", width: "45%" }
                                    ]
                                },
                                "strawberry": {
                                    name: "HMNS X",
                                    image: imagePaths.strawberryPerfume,
                                    accords: [
                                        { name: "sweet", color: "#ffb6c1", width: "88%" },
                                        { name: "musky", color: "#dcdcdc", width: "78%" },
                                        { name: "fruity", color: "#ffa07a", width: "68%" },
                                        { name: "powdery", color: "#e6e6fa", width: "58%" },
                                        { name: "green", color: "#ffd700", width: "48%" },
                                        { name: "woody", color: "#8b4513", width: "38%" }
                                    ]
                                },
                            };

                            let recommendation = "No recommendation available.";
                            let recommendationDetails = null;

                            // Find recommendation based on the classification name
                            for (const key in recommendations) {
                                if (classificationName.includes(key)) {
                                    recommendationDetails = recommendations[key];
                                    recommendation = recommendationDetails.name;
                                    break;
                                }
                            }

                            console.log("Recommendation Details:", recommendationDetails); // Log the recommendation details

                            resultHtml = `<div class="top-result-box">It's ${topResult.class} (Accuracy: ${accuracy}%)</div>`;
                            resultHtml += `<div class="recommendation-box">Recommendation: ${recommendation}</div>`;

                            // Check if recommendationDetails is available
                            if (recommendationDetails && recommendationDetails.accords) {
                                resultHtml += `
                                    <div class="detail-box">
                                        <div class="rec-image-box">
                                            <img src="${recommendationDetails.image}" alt="${recommendationDetails.name}" />
                                        </div>
                                        <div class="main-accords">
                                            <h4>Main Accords</h4>`;

                                // Generate accord bars based on the details in recommendationDetails.accords
                                recommendationDetails.accords.forEach(accord => {
                                    resultHtml += `<div class="accord-bar" style="background-color: ${accord.color}; width: ${accord.width};">${accord.name}</div>`;
                                });

                                resultHtml += `</div></div>`;
                            } else {
                                console.log("No recommendation details found for this classification.");
                            }
                        }

                        document.getElementById('classification-result').innerHTML = resultHtml;
                    }
                } else if (data.error) {
                    alert(data.error);
                }
            },
            error: function (xhr, status, error) {
                console.error('Upload error:', xhr.responseText);
                alert('Failed to upload image: ' + xhr.responseText);
            }
        });
    }

    document.getElementById('drop-area').addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        handleFiles(e.dataTransfer.files);
    });

    document.getElementById('drop-area').addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
    });
</script>

</body>
</html>
