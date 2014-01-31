v1.0 
- Initial Release

v1.01
- Removed unnecessary code
- Changed style of output (using print_r now)
- Improved url regex pattern (experimental)
- Improved email regex pattern (experimental)

V1.02
- Removed file_get_contents as this function was not designed to fetch HTML content and behaves unexpected on certain pages. CURL is the only way to go now.
- Every instance will print its results as soon as it finished, thus we don't have to pass the whole email array to each sub-instance => less overhead.
- Additional information provided on array output
- printResults works now properly
- Some really, really minor fixes (not worth mentioning)
