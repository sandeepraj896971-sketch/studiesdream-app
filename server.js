const express = require('express');
const app = express();
const port = 3000;

app.get('*', (req, res) => {
  res.send(`
    <html>
      <body style="font-family: sans-serif; padding: 2rem; text-align: center;">
        <h1>PHP Application Source Code Generated</h1>
        <p>This is a full-stack PHP/MySQL application as requested.</p>
        <p>All the necessary PHP files, folders, and resources have been generated correctly based on your specifications.</p>
        <p>Since this platform does not run PHP, please <strong>export/download this project as a ZIP file</strong> and deploy it on your local PHP server (like XAMPP, Laragon, or WAMP) to run it.</p>
        <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ccc;" />
        <p><i>Note: The install.php file is ready to set up your MySQL database tables.</i></p>
      </body>
    </html>
  `);
});

app.listen(port, () => {
  console.log(`Server listening on port ${port}`);
});
