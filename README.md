# Beamzer

This is a library that adds cross-browser support for real-time feeds and notifications to PHP web applications in an easy way (using Server-Sent Events (SSE) and COMET technologies only). It currently supports **Laravel** version 5.2 to 5.5 only. 

## How to Use

```bash

		$ composer require isocroft/beamzer


```

## License

MIT


## Addendum

I will be adding support on the client side with a simple JavaScript wrapper library called **BeamzerClient**. This library will depend on a polyfill for SSE (window.EventSource) maintained at this [repository](https://github.com/amvtek/EventSource/). Make sure you have the script at [this repository](https://github.com/isocroft/beamzer-client/) (**BeamzerClient**) before you use beamzer. This is because **Beamzer** and **BeamzerClient** work hand-in-hand.   
