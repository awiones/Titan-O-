const express = require('express');
const bodyParser = require('body-parser');
const app = express();

// Middleware
app.use(bodyParser.json());

// Enable CORS
app.use((req, res, next) => {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept');
    next();
});

// Bot endpoint
app.post('/bot', (req, res) => {
    const message = req.body.message;
    
    // You can implement more sophisticated bot logic here
    const responses = [
        "Interesting! Tell me more.",
        "I'm processing your request.",
        "That's a great point!",
        "Let me think about that for a moment.",
        "I can help you with that."
    ];

    const randomResponse = responses[Math.floor(Math.random() * responses.length)];
    
    // Simulate processing delay
    setTimeout(() => {
        res.json({ response: randomResponse });
    }, 1000);
});

// Start server
const PORT = 3000;
app.listen(PORT, () => {
    console.log(`Bot server running on port ${PORT}`);
});
