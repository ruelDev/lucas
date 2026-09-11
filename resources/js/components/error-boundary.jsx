/* eslint-disable react/prop-types */
import { Component } from 'react';

export default class ErrorBoundary extends Component {
    state = { error: null };

    static getDerivedStateFromError(error) {
        return { error };
    }

    render() {
        if (this.state.error) {
            return (
                <div
                    style={{
                        minHeight: '100vh',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        background: '#0a0a0f',
                        fontFamily: 'monospace',
                        color: '#fff',
                        padding: '2rem',
                    }}
                >
                    <div style={{ textAlign: 'center', maxWidth: 480 }}>
                        <div style={{ fontSize: '3rem', marginBottom: '1rem' }}>💥</div>
                        <h2 style={{ marginBottom: '.5rem' }}>React Render Error</h2>
                        <p style={{ color: '#94a3b8', marginBottom: '1.5rem' }}>{this.state.error.message}</p>
                        <button
                            onClick={() => window.location.reload()}
                            style={{
                                background: '#ef4444',
                                border: 'none',
                                color: '#fff',
                                padding: '.65rem 1.5rem',
                                borderRadius: '8px',
                                cursor: 'pointer',
                                fontSize: '.9rem',
                            }}
                        >
                            Reload Page
                        </button>
                    </div>
                </div>
            );
        }

        return this.props.children;
    }
}
