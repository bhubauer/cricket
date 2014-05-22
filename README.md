cricket
=======

Cricket is a newly open sourced, yet very rich and mature PHP web application framework.

Cricket's true innovation is that its not innovative! Cricket's architecture is as old as GUI computing. It is very similar to classic desktop GUI window and view hierarchies where the web page is analogous to the GUI's Window, and component objects are analogous to "sub views" of the window.

The web page and its (sub) components are backed one-to-one by object instances on the server. Each page and component class can be bundled with its own resources (templates, images, js, css, etc.), and a component can render itself independently of the page and all other components. This independence allows pages and components to be truly sharable and reusable. This has been proven with years of production use.

Its important to note that while Cricket is mature and has been used in production for quite some time, releasing the open source version will require a transition process. The documentation is still forth coming, and we are using this open source release as an opportunities to remove some of the crud that has been building up and to clean up and refactor some of the APIs.

