# HTTP Crafter

A lightweight http PHP library designed to simplify the creation, manipulation, and handling of HTTP requests and responses. Whether you're building APIs, interacting with web services, or scraping web content, HttpCrafter provides a robust and easy-to-use interface for all your HTTP needs.

## Roadmap

### Version 0.1.0 - Enhanced Release

- Core Features
  - Refactored Request and Response classes into src/Http/, improving file structure and accessibility.
  - Introduced RequestInterface.php and ResponseInterface.php to allow custom implementations for request and response handling.
  - Added the Router class from the archived oladesoftware/router repository, providing a foundational routing component.
- Test Suite Updates
  - Updated test files to reflect new directory structure:
  - tests/Http/RequestTest.php
  - tests/Http/ResponseTest.php
  - tests/Router/RouterTest.php
- Ongoing Maintenance
  - Regular updates for bug fixes, security patches, and documentation improvements.
  - Active issue tracking and community contributions encouraged.

### Version 0.0.0 - Initial Release

- Core Features
  - Request class to manage $_SERVER, $_GET and $_POST
  - Response class to build and send HTML or JSON 

- Documentation
  - Basic usage with examples

### Ongoing Maintenance

- Regular Updates
  - Bug fixes and security patches
  - Documentation improvements
- Community Engagement
  - Active issue tracking and resolution 
  - Encouraging community contributions

## Documentation

- [Installation](./docs/00-installation.md)
- [Usage](./docs/01-usage.md)

## Contributing

Contributions are welcome! Feel free to submit issues or pull requests.

## Support

For support, please open an issue on the GitHub repository.

## Contributors

- [Helmut](https://github.com/ahokponou)

## Author

- [Olade Software](https://oladesoftware.com)