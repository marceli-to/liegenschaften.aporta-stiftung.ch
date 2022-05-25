export default {
  url: "/api/file/upload",
  method: 'post',
  maxFilesize: 8,
  maxFiles: 1,
  createImageThumbnails: false,
  acceptedFiles: '.pdf, .doc, .docx, .zip, .xls, .xlsx',
  headers: {
    'Authorization': 'Bearer ' + localStorage.getItem('token')
  }
}