package proxy

import (
	"context"
	"io"
	"net/http"
	"net/url"
	"time"
)

// legacyTLSCiphers lists the cipher suites accepted by CentOS 6 / OpenSSL 1.0.1e.
// Go's modern default excludes TLS 1.0 ciphers, causing handshake failures against
// those hosts. This set covers both RSA and ECDHE key exchange with AES-CBC and
// 3DES so that the handshake succeeds while still using InsecureSkipVerify for
// self-signed certs common on legacy targets.
var legacyTLSCiphers = []uint16{
	tls.TLS_ECDHE_RSA_WITH_AES_128_CBC_SHA,
	tls.TLS_ECDHE_RSA_WITH_AES_256_CBC_SHA,
	tls.TLS_RSA_WITH_AES_128_CBC_SHA,
	tls.TLS_RSA_WITH_AES_256_CBC_SHA,
	tls.TLS_RSA_WITH_3DES_EDE_CBC_SHA,
}

// NewHTTPClient creates an http.Client that optionally routes through a proxy.
// proxyURL may be empty for no proxy. tc may be nil for sensible defaults.
// Set legacyTLS to true to support servers that only speak TLS 1.0 (e.g. CentOS 6).
func NewHTTPClient(proxyURL string, tc *TransportConfig, legacyTLS bool) http.Client {
	tlsCfg := newUpstreamTLSConfig("", nil)
	if legacyTLS {
		tlsCfg.MinVersion = tls.VersionTLS10 //nolint:gosec // intentional legacy support
		tlsCfg.CipherSuites = legacyTLSCiphers
	}

	var transport *http.Transport
	if tc != nil {
		// Clone settings from the TransportConfig but create a new transport
		// so we can safely set a proxy without mutating the shared one.
		transport = &http.Transport{
			ForceAttemptHTTP2: tc.HTTP2(),
			DisableKeepAlives: !tc.KeepAlive(),
			TLSClientConfig:   tlsCfg,
			DialContext:       tc.SOCKSDialContext(),
		}
	} else {
		transport = &http.Transport{
			ForceAttemptHTTP2: true,
			TLSClientConfig:   tlsCfg,
		}
	}

	if proxyURL != "" && proxyURL != "NOPROXY" {
		if u, err := url.Parse(proxyURL); err == nil {
			transport.Proxy = http.ProxyURL(u)
		}
	}

	return http.Client{
		Transport: transport,
		CheckRedirect: func(req *http.Request, via []*http.Request) error {
			return http.ErrUseLastResponse
		},
	}
}

// MakeRequest performs an HTTP request using the provided client with a configurable timeout.
func MakeRequest(method, target string, timeout int64, body io.Reader, client http.Client) ([]byte, string, int, error) {
	ctx, cancel := context.WithTimeout(context.Background(), time.Duration(timeout)*time.Second)
	defer cancel()

	req, err := http.NewRequestWithContext(ctx, method, target, body)
	if err != nil {
		return nil, "", 0, err
	}

	resp, err := client.Do(req)
	if err != nil {
		return nil, "", 0, err
	}
	defer resp.Body.Close()

	bodyBytes, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, "", 0, err
	}

	return bodyBytes, string(bodyBytes), resp.StatusCode, nil
}
