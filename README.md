# Dynamite Images
🎨⚡📝: DYNAmically Modeled Image + Text Experience

Dynamite Images is a project that allows you to dynamically pass styled, personalized text directly into images for use in personalized materials like emails.

It is a self-contained Google App Engine app that will allow you to stand up your own instance and only pay for the usage you need. It contains both the image manipulation API, as well as a client for building image URLs.

<h2>Installation</h2>

Simply create a Google App Engine app and deploy it, with your desired font files placed in the `fonts` folder and your images contained in the `img` folder

In order to support uploads of files outside of the `img` folder (ie, to allow you to upload new base images without having to re-deploy the app each time)

1. Enable billing on your GAE app.
2. Enable Google Cloud Storage.
3. To give other users permission to upload images, you'll need to add them as administrators to your application.

The display/edit part of the code can be run locally or on a regular php server without GAE integration as long as the url parameter "local" is set to true (i.e. `http://localhost:4007/BSD-logo.png?local=true&text=Using%20a%20local%20image%20from%20the%20/img/%20directory&max-width=400&white-space=normal&top=50&text-align=center` ) and images in the local /img/ are referenced.

<h2>API</h2>
By default, the API works by passing parameters to an image's path. For example, if your app is `your-demo-app`, and you upload `banner.png`, your initial API endpoint will be `your-demo-app.appspot.com/banner.png` and `your-demo-app.appspot.com/banner.png?text=Example&top=100` would return an image with that text printed on it, 100 pixels from the top. You can specify the url parameters either manually, or use the client packaged at `/edit.html` to set the parameters and get the final link.

Where possible, the API's parameters follow CSS naming conventions, for familiarity and simplicity (this is just for convenience; the API is not real a CSS parser.)

<h3>Fonts</h3>
By default, the repository comes packaged with a couple of liberally licensed fonts. If you want to include your own fonts, simply add the `.ttf` file for that font to the `fonts` folder and re-deploy your app.  The font should then be available for use and automatically appear in the dropdown list of `edit.html`.

<h3>URL Parameters</h3>

All unit measurements are specified in pixels except for line-height. Currently supported parameters:

- `text`: The text string to include in the images.
- `left`: Horizontal/X offset, in pixels. (e.g., `10`)
- `top`: Vertical/Y offset, in pixels. (e.g., `30`)
- `font-size`: Font size in pixels (e.g., `24`)
- `font-family`: The name of the font, without the file suffix. (e.g., `OpenSans-Regular`).
- `color`: A hexidecimal code for the image (e.g., `#F0F0F0`)
- `text-transform`: Alter the text's capitalization. Supported values: `uppercase`, `lowercase`, and `capitalize`.
- `text-align`: Horizontal alignment across the entire image (offset by the `left` value). Defaults to `left`. Additionally supported values: `center`, `right`.
- `vertical-align`: Vertical alignment of the text, relative to the `top` offset. Defaults to `top`. Supported Values: `top`, `middle`, `bottom`.
- `white-space`: Manage text wrapping. Supported values: `nowrap` (default), and `normal`, which will wrap text to the next line when it overflows the image.
- `line-height`: Adds spacing between lines when they wrap (when white-space = normal or manual line-breaks are included in the text)
- `max-width`: When wrapping is enabled (white-space = normal), restricts the total width of the text block. Using 0 + white-space = normal defaults to wrapping lines at the edges of the image.
- `text-shadow`: 4 parameters: the left offset, top offset, color, and opacity of a text-shadow effect
- `outline`: 3 parameters: the spread (distance), color, and opacity of a text glow effect

All parameters are optional though most have defaults set for any particular rendered string of text (positioning, color, etc.) The final url must be properly urlencoded (e.g. newlines encoded as %0A, spaces as %20, etc.).  The included `edit.html` will encode your selected parameters automatically and is a good place to experiment with the syntax.

You can add more than one text layer by utilizing the URL parameter array notation. e.g., you could use `?text[0]=FirstLayer&left[0]=400&text[1]=SecondLayer&left[1]=300` to specify the configuration settings for each layer within its array index.  Either specify each array index explicitly (`text[0]=1&text[1]=2`) or implicitly (`text[]=1&text[]=2`) but do not mix the two approaches.  Again, experiment with `edit.html` to get a sense of how multiple layers work.  The local and debug parameters are exceptions: they should not be passed as array parameters.

Finally, clicking on the preview image `edit.html` will set the X/Y offset, placing the text above, below, left of, right of, or centered on a point according to the text and vertical alignment settings.

<h3>Text Encoding</h3>

By default, the copyable `Output Code` from the `edit.html` will not fully urlencode certain templating tags, leaving the tags and spaces inside of them intact.  This allows you to paste that code into a batch mailer that supports personalization/tag interpretation such that every user will get an image customized with their own data. The tags formats currently included are:
- `{{ EXAMPLE }}`: i.e. a starting delimiter `{{`, any internal spaces or code, and then an ending delimiter `}}`
- `%%EXAMPLE%%`: i.e. a starting delimiter `%%`, any internal spaces or code, and then an ending delimiter `%%`
- `{% EXAMPLE %}`: i.e. a starting delimiter `{%`, any internal spaces or code, and then an ending delimiter `%}`

<h2>Important considerations</h2>
Some older browsers/email clients have an effective maximum character length on url strings ([usually 2048](http://stackoverflow.com/questions/417142/what-is-the-maximum-length-of-a-url-in-different-browsers#answer-417184)).  The editor will warn you about any that begin to exceed that length, but especially with customized data that can vary in length, it pays to be conservative.

Some older email clients also have an effective maximum height (Outlook primarily: [more here](https://www.campaignmonitor.com/blog/post/3103/maximum-height-for-images-in-email-outlook/) [and here](http://stackoverflow.com/questions/2419388/need-workaround-for-outlook-2007-html-email-rendering-bug-horizontal-gaps/5662156#5662156)) of around 1728 pixels. If you're creating an email design with a single very long image that comes close to or exceeds that length, it's best to break that into several smaller images (which may also have some "lazy loading" benefits in emails for clients that download each image in turn when only the first is visible above the fold).

<h2>Local Development & Contributing</h2>

The (rudimentary) upload/editor interfaces are built with [Bootstrap](http://getbootstrap.com/) and gulp.

Run
```
npm install
```
and then
```
gulp
```
to compile the css and js files.