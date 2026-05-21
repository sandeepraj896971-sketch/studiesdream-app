const http = require('http');
const req = http.request('http://localhost:3000/admin/course.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded'
  }
}, (res) => {
  res.on('data', d => process.stdout.write(d));
});
req.write('save=1&title=Test&mrp=100&price=50&description=Desc');
req.end();
